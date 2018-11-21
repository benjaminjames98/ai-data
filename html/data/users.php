<?php
include_once(dirname(__FILE__) . '/../../includes/db_connect.php');
include_once(dirname(__FILE__) . '/../../includes/login_functions.php');

sec_session_start();

$PAGE_TYPE = 'per_users';
$PAGE_RECONCILER = '';

if (!permission_check($mysqli, $PAGE_TYPE)) {
  header('Location: index.php');
  exit(0);
} ?>
<!DOCTYPE html>
<html>
<head>
  <title>Users</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="../../styles/main.css">
  <link rel="stylesheet" href="../../styles/w3.css">
  <script type="text/JavaScript" src="../../js/sha512.js"></script>
  <script type="text/JavaScript" src="../../js/utils.js"></script>
  <script type="text/JavaScript" src="../../js/create_new_user.js"></script>
  <script type="text/JavaScript" src="../../js/user_permission_controller.js"></script>
</head>
<body class="w3-light-grey">

<?php include("head_nav.php") ?>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:300px;margin-top:43px;">

  <header class="w3-container" style="padding-top:22px">
    <h5><b><i class="fas fa-users-cog fa-fw"></i> Users</b></h5>
  </header>

  <div class="w3-panel">
    <div class="w3-row-padding" style="margin:0 -16px">
      <div class="w3-half">
        <h5>New User</h5>
        <label for="username">Username:</label>
        <input name='username' id='new_username' type='text' class="w3-input w3-border-bottom w3-border-green"/>
        <br>
        <label for="email">Email:</label>
        <input name="email" id="new_email" type="text" class="w3-input w3-border-bottom w3-border-red"/>
        <br>
        <label for="password">Password:</label>
        <input name="password" id="new_password" type="password" class="w3-input w3-border-bottom w3-border-teal"/>
        <br>
        <label for="confirmpwd">Confirm password:</label>
        <input name="confirmpwd" id="new_confirmpwd" type="password"
               class="w3-input w3-border-bottom w3-border-orange"/>
        <br>
        <input type="button" id="new_button" value="Register" style="width:100%" class="w3-btn w3-green"/>
      </div>
      <div class="w3-half">
        <h5>Permissions</h5>
        <label for="user">User:</label>
        <select name="user" id="per_user" class="w3-select w3-white w3-border-green"> </select>
        <div id="per_container"></div>
        <input type="button" id="per_button" value="Save Changes" style="width:100%" class="w3-btn w3-green"/>
      </div>
    </div>
  </div>

  <!-- End page content -->
</div>

<script>
  window.onload = function () {
    UserPermissionController($('per_user'), $('per_container'), $('per_button'));
    $('new_button').onclick = createNewUser;
  }
</script>

</body>
</html>