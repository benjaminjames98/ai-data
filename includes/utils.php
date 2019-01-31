<?php

function read_usernames() {
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
