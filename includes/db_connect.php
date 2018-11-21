<?php
$mysqli = new mysqli('localhost', 'benclo5_temp_user', '4nczSj4nczSj', 'benclo5_temp');

function getMysqli() {
  $mysqli = new mysqli('localhost', 'benclo5_temp_user', '4nczSj4nczSj', 'benclo5_temp');

  if ($mysqli->connect_errno)
    return -1;
  else return $mysqli;
}
