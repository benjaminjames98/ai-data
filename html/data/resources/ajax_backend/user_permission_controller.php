<?php
include_once '../../../../includes/db_connect.php';

function throwError ($msg = '') {
  die(json_encode(["a" => "0", "msg" => "error in: " . $msg]));
}

$data = json_decode($_REQUEST['user']);
$q = $data->q;

if ($q == 'read_usernames') read_usernames();
else if ($q == 'read_permissions') read_permissions();
else if ($q == 'update_permissions') update_permissions();

function read_usernames () {
  global $mysqli;
  $query = "SELECT username FROM login_user";
  if (!$stmt = $mysqli->prepare($query)) throwError("read_roles");
  $stmt->execute();
  $stmt->bind_result($username);
  $arr = [];
  while ($stmt->fetch()) $arr[] = $username;
  $stmt->close();

  die(json_encode(["success" => true, "usernames" => $arr]));
}

function read_permissions () {
  global $mysqli, $data;
  $username = $data->username;
  if (!isset($username)) throwError('please supply all inputs');
  $username = filter_var($username, FILTER_SANITIZE_STRING);

  $prep_stmt = "SELECT per_users, per_education, per_bread, per_churches FROM login_user WHERE username = ?";
  $stmt = $mysqli->prepare($prep_stmt);
  if (!$stmt) throwError('Database error at ' . __LINE__);


  $stmt->bind_param('s', $username);
  $stmt->execute();
  $stmt->bind_result($usr, $edu, $brd, $chc);
  $stmt->fetch();
  $perms = ['per_users' => boolval($usr), 'per_education' => boolval($edu), 'per_bread' => boolval($brd),
    'per_churches' => boolval($chc)];

  die(json_encode(["success" => true, "username" => $username, 'permissions' => $perms]));
}

function update_permissions () {
  global $mysqli, $data;
  $username = $data->username;
  $perms = (array)$data->permissions;

  if (!isset($username, $perms)) throwError('please supply all inputs');
  $username = filter_var($username, FILTER_SANITIZE_STRING);
  $perms = array_filter($perms, function ($v, $k) {
    $permission_types = ['per_users', 'per_churches', 'per_education', 'per_bread'];
    return in_array($k, $permission_types) || is_bool($v);
  }, ARRAY_FILTER_USE_BOTH);

  foreach ($perms as $k => $v) {
    $prep_stmt = "UPDATE login_user SET $k = ? WHERE username = ?";
    $stmt = $mysqli->prepare($prep_stmt);
    if (!$stmt) throwError('Database error at ' . __LINE__ . ': ' . $mysqli->error);
    $stmt->bind_param('ss', $v, $username);
    $stmt->execute();
    $stmt->close();
  }

  die(json_encode(["success" => true]));
}

throwError('end of file');