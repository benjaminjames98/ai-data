<?php
include_once(dirname(__FILE__) . '/../../includes/db_connect.php');
include_once(dirname(__FILE__) . '/../../includes/login_functions.php');

$per_users = permission_check($mysqli, 'per_users');
$per_churches = permission_check($mysqli, 'per_churches');
$per_education = permission_check($mysqli, 'per_education');
$per_bread = permission_check($mysqli, 'per_bread');

$PAGE_RECONCILER = isset($PAGE_RECONCILER) ? $PAGE_RECONCILER : '';
?>

<!-- Top container -->
<div class="w3-bar w3-top w3-black w3-large" style="z-index:4">
    <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey"
            onclick="w3_open();"><i
                class="fa fa-bars"></i> Menu
    </button>
    <span class="w3-bar-item w3-right">Logo</span>
</div>

<!-- Sidebar/menu -->
<nav class="w3-sidebar w3-collapse w3-white w3-animate-left"
     style="z-index:3;width:300px;" id="mySidebar"><br>
    <div class="w3-container w3-row">
        <div class="w3-col s4">
            <div class="w3-circle w3-margin-right w3-black w3-display-container"
                 style="width:46px; height: 46px">
                <i class="fas fa-user fa-fw w3-xlarge w3-text-white w3-display-middle"></i>
            </div>
        </div>
        <div class="w3-col s8 w3-bar">
            <span>Welcome, <strong><?php echo htmlentities($_SESSION['username']) ?></strong></span>
        </div>
    </div>
    <hr>
    <div class="w3-bar-block">
        <a href="#"
           class="w3-bar-item w3-button w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black"
           onclick="w3_close()" title="close menu"><i
                    class="fa fa-remove fa-fw"></i> Close Menu</a>
        <div class="w3-bar-item">
            <h5>Dashboard</h5>
        </div>
        <a href="<?= $PAGE_RECONCILER ?>dashboard.php"
           class="w3-bar-item w3-button w3-padding">
            <i class="fas fa-chart-line fa-fw"></i>Dashboard</a>
      <?php if ($PAGE_TYPE == 'per_users') : ?>
          <a href="<?= $PAGE_RECONCILER ?>users.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-users-cog fa-fw"></i> Users</a>
      <?php endif; ?>

        <hr>
        <div class="w3-bar-item">
            <h5>Other Pages</h5>
        </div>
      <?php if ($per_users) : ?>
          <a href="<?= $PAGE_RECONCILER ?>users.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-users-cog fa-fw"></i> Users</a>
      <?php endif; ?>
      <?php if ($per_churches) : ?>
          <a href="<?= $PAGE_RECONCILER ?>churches/churches_frontend.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-church fa-fw"></i> Churches</a>
      <?php endif;
      if ($per_education) : ?>
          <a href="<?= $PAGE_RECONCILER ?>cohorts/education_frontend.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-graduation-cap fa-fw"></i> Cohorts</a>
      <?php endif;
      if ($per_bread) : ?>
          <a href="<?= $PAGE_RECONCILER ?>bread/bread_churches_frontend.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-cookie-bite fa-fw"></i> Bread - Management</a>
          <a href="<?= $PAGE_RECONCILER ?>bread/bread_status_all_frontend.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-cookie-bite fa-fw"></i> Bread - State</a>
          <a href="<?= $PAGE_RECONCILER ?>bread/bread_status_frontend.php"
             class="w3-bar-item w3-button w3-padding">
              <i class="fas fa-cookie-bite fa-fw"></i> Bread - Regional</a>
      <?php endif; ?>
        <hr>
        <a href="<?= $PAGE_RECONCILER ?>resources/ajax_backend/login_logout.php"
           class="w3-bar-item w3-button w3-padding">
            <i class="fas fa-sign-out-alt fa-fw"></i> Logout</a>
        <br><br>
    </div>
</nav>

<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()"
     style="cursor:pointer"
     title="close side menu" id="myOverlay"></div>

<script>
  // Get the Sidebar
  let mySidebar = document.getElementById('mySidebar');

  // Get the DIV with overlay effect
  let overlayBg = document.getElementById('myOverlay');

  // Toggle between showing and hiding the sidebar, and add overlay effect
  function w3_open() {
    if (mySidebar.style.display === 'block') {
      mySidebar.style.display = 'none';
      overlayBg.style.display = 'none';
    } else {
      mySidebar.style.display = 'block';
      overlayBg.style.display = 'block';
    }
  }

  // Close the sidebar with the close button
  function w3_close() {
    mySidebar.style.display = 'none';
    overlayBg.style.display = 'none';
  }
</script>