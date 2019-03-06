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
  <link rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="resources/styles/main.css">
  <link rel="stylesheet" href="resources/styles/w3.css">
  <script type="text/JavaScript" src="resources/js/login_sha512.js"></script>
  <script type="text/JavaScript" src="resources/js/utils.js"></script>
  <script type="text/JavaScript"
          src="resources/js/create_new_user.js"></script>
  <script type="text/JavaScript"
          src="resources/js/user_permission_controller.js"></script>
  <script type="text/JavaScript"
          src="resources/js/change_password_controller.js"></script>
  <script> const PAGE_RECONCILER = '<?php echo $PAGE_RECONCILER ?>'; </script>
</head>
<body class="w3-light-grey">

<?php include("head_nav.php") ?>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:300px;margin-top:43px;">

  <header class="w3-container" style="padding-top:22px">
    <h5><b><i class="fas fa-users-cog fa-fw"></i> Users</b></h5>
  </header>

  <div class="w3-panel">
    <div class="w3-row" style="margin:0 -16px">
      <div class="w3-half">
        <div class="w3-card w3-padding w3-margin w3-white">
          <h5>New User</h5>
          <label for="username">Username:</label>
          <input name='username' id='new_username' type='text'
                 class="w3-input w3-border-bottom w3-border-green"
                 title="enter username"/>
          <br>
          <label for="email">Email:</label>
          <input name="email" id="new_email" type="text"
                 class="w3-input w3-border-bottom w3-border-red"
                 title="enter email"/>
          <br>
          <label for="password">Password:</label>
          <input name="password" id="new_password" type="password"
                 class="w3-input w3-border-bottom w3-border-teal"
                 title="enter password"/>
          <br>
          <label for="confirmpwd">Confirm password:</label>
          <input name="confirmpwd" id="new_confirmpwd" type="password"
                 class="w3-input w3-border-bottom w3-border-orange"
                 title="confirm password"/>
          <br>
          <input type="button" id="new_button" value="Register"
                 style="width:100%" class="w3-btn w3-green"/>
        </div>
      </div>
      <div class="w3-half">
        <div class="w3-card w3-padding w3-margin w3-white">
          <h5>Permissions</h5>
          <label for="user">User:</label>
          <select name="user" id="per_user"
                  class="w3-select w3-white w3-border-green"
                  title="Select user to display"> </select>
          <div id="per_container"></div>
          <br>
          <input type="button" id="per_button" value="Save Changes"
                 style="width:100%" class="w3-btn w3-green"/>
        </div>
      </div>
      <div class="w3-half">
        <div class="w3-card w3-padding w3-margin w3-white">
          <h5>Change Password</h5>
          <label for="chg_user">User:</label>
          <select name="chg_user" id="chg_user"
                  class="w3-select w3-white w3-border-green"
                  title="Select user to display"> </select>
          <br><br>
          <label for="chg_password">Password:</label>
          <input name="chg_password" id="chg_password" type="password"
                 class="w3-input w3-border-bottom w3-border-teal"
                 title="enter password"/>
          <br>
          <label for="chg_confirmpwd">Confirm password:</label>
          <input name="chg_confirmpwd" id="chg_confirmpwd" type="password"
                 class="w3-input w3-border-bottom w3-border-orange"
                 title="confirm password"/>
          <br>
          <input type="button" id="chg_button" value="Change Password"
                 style="width:100%" class="w3-btn w3-green"/>
        </div>
      </div>
    </div>
  </div>

  <!-- End page content -->
</div>

<script>
  window.onload = function() {
    UserPermissionController(el('per_user'), el('per_container'),
      el('per_button'));
    el('new_button').onclick = createNewUser;
    ChangePasswordController(el('chg_user'), el('chg_password'),
      el('chg_confirmpwd'), el('chg_button'));
  };
</script>

</body>
</html>