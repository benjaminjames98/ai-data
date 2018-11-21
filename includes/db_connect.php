<?php

function getMysqli() {
  $mysqli = new mysqli('localhost', 'cnetuser', '4nczSj4nczSj', 'cnetdata');

  if ($mysqli->connect_errno)
    return -1;
  else return $mysqli;
}

$mysqli = getMysqli();
