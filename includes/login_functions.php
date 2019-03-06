<?php

function sec_session_start() {
  // Set a custom session name
  $session_name = 'citynetworks_login';
  // This determines whether the page requires https
  $secure = false;
  // This stops JavaScript being able to access the session id.
  $httponly = false;
  // Forces sessions to only use cookies.
  if (ini_set('session.use_only_cookies', 1) === false) {
    header("Location: ../www/data/error.php?err=Could not initiate a safe session (ini_set)");
    exit();
  }
  // Gets current cookies params.
  $cookieParams = session_get_cookie_params();
  session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"],
    $cookieParams["domain"], $secure, $httponly);
  // Sets the session name to the one set above.
  session_name($session_name);
  session_start();            // Start the PHP session
  session_regenerate_id();    // regenerated the session, delete the old one.
}

/**
 * @param        $email
 * @param        $password
 * @param mysqli $mysqli
 *
 * @return bool
 */
function login($email, $password, mysqli $mysqli) {
  // Using prepared statements means that SQL injection is not possible.
  if ($stmt = $mysqli->prepare("SELECT `__pk_id`, username, password 
        FROM login_user
       WHERE email = ?
        LIMIT 1")) {
    $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
    $stmt->execute();    // Execute the prepared query.
    $stmt->store_result();

    // get variables from result.
    $user_id = $username = $db_password = null;
    $stmt->bind_result($user_id, $username, $db_password);
    $stmt->fetch();

    if ($stmt->num_rows == 1) {
      // If the user exists we check if the account is locked
      // from too many login attempts

      if (checkbrute($user_id, $mysqli) == true) {
        // Account is locked
        // Send an email to user saying their account is locked
        return false;
      } else {
        // Check if the password in the database matches
        // the password the user submitted. We are using
        // the password_verify function to avoid timing attacks.
        if (password_verify($password, $db_password) /*|| true*/) {
          // Password is correct!
          // Get the user-agent string of the user.
          $user_browser = $_SERVER['HTTP_USER_AGENT'];
          // XSS protection as we might print this value
          $user_id = preg_replace("/[^0-9]+/", "", $user_id);
          $_SESSION['user_id'] = $user_id;
          // XSS protection as we might print this value
          $username = preg_replace("/[^a-zA-Z0-9_\-]+/",
            "",
            $username);
          $_SESSION['username'] = $username;
          $_SESSION['login_string'] = hash('sha512',
            $db_password . $user_browser);
          // Login successful.
          return true;
        } else {
          // Password is not correct
          // We record this attempt in the database
          $now = time();
          $mysqli->query("INSERT INTO login_attempts(`_fk_login_user`, time)
                                    VALUES ('$user_id', '$now')");
          return false;
        }
      }
    } else {
      // No user exists.
      return false;
    }
  }
  return false;
}

function checkbrute($user_id, mysqli $mysqli) {
  // Get timestamp of current time
  $now = time();

  // All login attempts are counted from the past 2 hours.
  $valid_attempts = $now - (2 * 60 * 60);

  if ($stmt = $mysqli->prepare("SELECT time 
                             FROM login_attempts 
                             WHERE `_fk_login_user` = ? 
                             AND time > '$valid_attempts'")) {
    $stmt->bind_param('i', $user_id);

    // Execute the prepared query.
    $stmt->execute();
    $stmt->store_result();

    // If there have been more than 5 failed logins
    if ($stmt->num_rows > 5) {
      return true;
    } else {
      return false;
    }
  }
  return false;
}

function login_check(mysqli $mysqli) {
  // Check if all session variables are set
  if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {

    $user_id = $_SESSION['user_id'];
    $login_string = $_SESSION['login_string'];

    // Get the user-agent string of the user.
    $user_browser = $_SERVER['HTTP_USER_AGENT'];

    if ($stmt =
      $mysqli->prepare("SELECT password FROM login_user WHERE `__pk_id` = ? LIMIT 1")) {
      // Biuser_ider_id" to parameter.
      $stmt->bind_param('i', $user_id);
      $stmt->execute();   // Execute the prepared query.
      $stmt->store_result();

      if ($stmt->num_rows == 1) {
        // If the user exists get variables from result.
        $password = null;
        $stmt->bind_result($password);
        $stmt->fetch();
        $login_check = hash('sha512', $password . $user_browser);

        if (hash_equals($login_check, $login_string)) {
          // Logged In!!!!
          return true;
        } else {
          // Not logged in
          return false;
        }
      } else {
        // Not logged in
        return false;
      }
    } else {
      // Not logged in
      return false;
    }
  } else {
    // Not logged in
    return false;
  }
}

function permission_check(mysqli $mysqli, $permission) {
  if (!login_check($mysqli)) return false;
  if (!in_array($permission,
    ['per_users', 'per_churches'])) return false;

  //login_check ensures that $_SESSION['username'] is set
  $username = $_SESSION['username'];

  $query = "SELECT $permission FROM login_user WHERE username = ? LIMIT 1";
  if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param('s', $username);
    $stmt->execute();   // Execute the prepared query.
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      // If the user exists get variables from result.
      $per = null;
      $stmt->bind_result($per);
      $stmt->fetch();
      return $per;
    } else {
      // Username doesn't exist. Shouldn't ever happen.
      return false;
    }
  } else {
    // Query error
    return false;
  }
}

function esc_url($url) {

  if ('' == $url) {
    return $url;
  }

  $url =
    preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

  $strip = ['%0d', '%0a', '%0D', '%0A'];
  $url = (string)$url;

  $count = 1;
  while ($count) {
    $url = str_replace($strip, '', $url, $count);
  }

  $url = str_replace(';//', '://', $url);

  $url = htmlentities($url);

  $url = str_replace('&amp;', '&#038;', $url);
  $url = str_replace("'", '&#039;', $url);

  if ($url[0] !== '/') {
    // We're only interested in relative links from $_SERVER['PHP_SELF']
    return '';
  } else {
    return $url;
  }
}