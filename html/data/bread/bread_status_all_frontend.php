<?php
include_once(dirname(__FILE__).'/../../../includes/db_connect.php');
include_once(dirname(__FILE__).'/../../../includes/login_functions.php');

sec_session_start();

if (!permission_check($mysqli, 'per_bread')) {
  header('Location: ../index.php');
  exit(0);
} ?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Bread Status</title>
  <link rel='stylesheet' href='../resources/styles/w3.css'>
  <link rel='stylesheet' href='../resources/styles/w3-theme-red.css'>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src='bread_status_all.js'></script>
</head>
<body>
<div id='div_switch' class='w3-card w3-margin w3-padding w3-row'>
</div>
</body>
</html>