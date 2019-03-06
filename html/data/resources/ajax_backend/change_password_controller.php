<?php
include_once '../../../../includes/db_connect.php';
include_once '../../../../includes/utils.php';

function throwError($msg = '') {
  die(json_encode(["a" => "0", "msg" => "error in: " . $msg]));
}

$data = json_decode($_REQUEST['user']);
$q = $data->q;

if ($q == 'read_usernames') read_usernames();
elseif ($q == 'change_password') change_password();

function change_password() {
  global $mysqli, $data;
  $usr = $data->usr;
  $pwd = $data->pwd;

  if (!isset($usr, $pwd)) throwError('please supply all inputs');
  $usr = filter_var($usr, FILTER_SANITIZE_STRING);
  $pwd = filter_var($pwd, FILTER_SANITIZE_STRING);

  // Create hashed password using the password_hash function.
  // This function salts it with a random salt and can be verified with the password_verify function.
  if (strlen($pwd) != 128) {
    // The hashed pwd should be 128 characters long. If it's not, something really odd has happened
    throwError("Invalid password configuration: ${pwd}");
  }

  $pwd = password_hash($pwd, PASSWORD_BCRYPT);

  $prep_stmt = "UPDATE login_user SET password = ? WHERE username = ?";
  $stmt = $mysqli->prepare($prep_stmt);
  if (!$stmt) throwError('Database error at ' . __LINE__ . ': '
    . $mysqli->error);
  $stmt->bind_param('ss', $pwd, $usr);
  $stmt->execute();
  $stmt->close();

  die(json_encode(["success" => true]));
}

throwError('end of file');