<?php
include_once(dirname(__FILE__) . '/../../includes/db_connect.php');
include_once(dirname(__FILE__) . '/../../includes/login_functions.php');

sec_session_start();

if (login_check($mysqli)) {
  header('Location: dashboard.php');
  exit(0);
} ?>
<!DOCTYPE html>
<html>
<head>
    <title>Secure Login: Log In</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=width, initial-scale=1">
    <link rel="stylesheet" href="resources/styles/w3.css"/>
    <link rel="stylesheet" href="resources/styles/w3-theme-green.css"/>
    <link rel="stylesheet" href="resources/styles/main.css"/>
    <script type="text/JavaScript" src="resources/js/sha512.js"></script>
    <script type="text/JavaScript" src="resources/js/forms.js"></script>
    <style>
        @media (max-width: 336px) {
            #login-card {
                width: 100%;
            }

        }
        @media (min-width: 337px) {
            #login-card {
                width: 336px;
                box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.19);
                position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);-ms-transform: translate(-50%, -50%);
            }
            body { background-color: #f1f1f1; }
        }

    </style>
</head>
<body>
<div id="login-card" class="w3-display-middle w3-white">
    <header class="w3-container">
        <br>
        <h1 class="w3-center">Login Page</h1>
    </header>
    <div class="w3-container w3-white">
        <div class="w3-panel">
            <form action="resources/ajax_backend/login_process.php"
                  method="post" name="login_form">
                <div class="w3-panel">
                    <label>Email:</label>
                    <input class="w3-input w3-border-bottom w3-border-green"
                           type="text" name="email"/>
                </div>
                <div class="w3-panel">
                    <label>Password:</label>
                    <input class="w3-input w3-border-bottom w3-border-green"
                           type="password" name="password" id="password"/>
                </div>
                <div class="w3-panel">
                    <input class="w3-btn w3-theme" type="button" value="Login"
                           style="width: 100%"
                           onclick="formhash(this.form, this.form.password);"/>
                </div>
              <?php if (isset($_GET['error'])) echo "<p class='w3-text-red'>Error Logging In!</p>"; ?>
            </form>
        </div>
    </div>
</div>
</body>
</html>