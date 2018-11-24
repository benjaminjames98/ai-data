<?php
include_once '../../../../includes/db_connect.php';
include_once '../../../../includes/login_functions.php';

sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['email'], $_POST['p'])) {
  $email = $_POST['email'];
  $password = $_POST['p']; // The hashed password.

  if (login($email, $password, $mysqli)) {
    // Login success
    header('Location: ../../app_hub.php');
  } else {
    // Login failed
    header('Location: ../../index.php?error=1');
  }
} else {
  // The correct POST variables were not sent to this page.
  echo 'Invalid Request';
}
