<?php
include_once 'db_connect.php';
include_once 'login_functions.php';

sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['email'], $_POST['p'])) {
    $email = $_POST['email'];
    $password = $_POST['p']; // The hashed password.

    if (login($email, $password, $mysqli)) {
        // Login success
        header('Location: ../www/data/app_hub.php');
    } else {
        // Login failed
        header('Location: ../www/data/index.php?error=1');
    }
} else {
    // The correct POST variables were not sent to this page.
    echo 'Invalid Request';
}
