<?php
include_once(dirname(__FILE__) . '/../../../includes/db_connect.php');
include_once(dirname(__FILE__) . '/../../../includes/login_functions.php');

sec_session_start();

$PAGE_TYPE = 'per_bread';
$PAGE_RECONCILER = '../';

if (!permission_check($mysqli, $PAGE_TYPE)) {
  header('Location: https://bencloud.tech/citynetworks_login/www/data/');
  exit(0);
} ?>
<!DOCTYPE html>
<html>
<head>
  <title>Bread - Churches</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="../resources/styles/main.css">
  <link rel="stylesheet" href="../resources/styles/w3.css">
  <script type="text/JavaScript" src="../resources/js/sha512.js"></script>
  <script type="text/JavaScript" src="../resources/js/utils.js"></script>
  <script type="text/JavaScript" src="../resources/js/route_list_controller.js"></script>
  <script> const PAGE_RECONCILER = '<?php echo $PAGE_RECONCILER ?>'; </script>
</head>
<body class="w3-light-grey">

<?php include("../head_nav.php") ?>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:300px;margin-top:43px;">

  <header class="w3-container" style="padding-top:22px">
    <h5><b><i class="fas fa-users-cog fa-fw"></i> Users</b></h5>
  </header>

  <div class="w3-panel">
    <div class="w3-bar w3-green w3-card w3-round-large">
      <div class="w3-bar-item">
        <select id="fil_route" class="w3-select">
        </select>
      </div>
      <div class="w3-bar-item">
        <select id="fil_participating" class="w3-select">
        </select>
      </div>

    </div>
    <ul id="church_container" class="w3-ul">

    </ul>
  </div>
  <!-- End page content -->
</div>

<script>
  window.onload = function () {
    RouteListController('<?= htmlentities($_SESSION['username']) ?>', el('church_container'),
      el('fill_participating'), el('fil_route'));
  }
</script>

</body>
</html>