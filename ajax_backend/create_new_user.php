<?php
include_once '../includes/db_connect.php';

$user = json_decode($_REQUEST['user']);
$username = $user->username;
$email = $user->email;
$p = $user->p;

if (isset($username, $email, $p)) {
  // Sanitize and validate the data passed in
  $username = filter_var($username, FILTER_SANITIZE_STRING);
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  $email = filter_var($email, FILTER_VALIDATE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Not a valid email
    throwError('The email address you entered is not valid');
  }

  $password = filter_var($p, FILTER_SANITIZE_STRING);
  if (strlen($password) != 128) {
    // The hashed pwd should be 128 characters long. If it's not, something really odd has happened
    throwError('Invalid password configuration.');
  }

  // Username validity and password validity have been checked client side.
  // This should should be adequate as nobody gains any advantage from
  // breaking these rules.
  $prep_stmt = "SELECT `__pk_id` FROM login_user WHERE email = ? LIMIT 1";
  $stmt = $mysqli->prepare($prep_stmt);

  // check existing email
  if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      // A user with this email address already exists
      $stmt->close();
      throwError('A user with this email address already exists.');
    }
  } else {
    $stmt->close();
    throwError('Database error Line 34');
  }

  // check existing username
  $prep_stmt = "SELECT `__pk_id` FROM login_user WHERE username = ? LIMIT 1";
  $stmt = $mysqli->prepare($prep_stmt);

  if ($stmt) {
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      // A user with this username already exists
      $stmt->close();
      throwError('A user with this username already exists.');
    }
  } else {
    $stmt->close();
    throwError('Database error line 55');
  }

  // Create hashed password using the password_hash function.
  // This function salts it with a random salt and can be verified with the password_verify function.
  $password = password_hash($password, PASSWORD_BCRYPT);

  // Insert the new user into the database
  if ($insert_stmt = $mysqli->prepare("INSERT INTO login_user (username, email, password) VALUES (?, ?, ?)")) {
    $insert_stmt->bind_param('sss', $username, $email, $password);
    // Execute the prepared query.
    if (!$insert_stmt->execute()) {
      throwError('Server issues. Please try again later.');
    }
  }
  die(json_encode(["success" => true]));

}

throwError('end of file');

function throwError($msg = '') {
  die(json_encode(["a" => "0", "msg" => "error in: " . $msg]));
}