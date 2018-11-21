<?php
$error = filter_input(INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING);

if (!$error) {
  $error = 'Oops! An unknown error happened.';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secure Login: Error</title>
  <link rel="stylesheet" href="resources/styles/w3.css">
</head>
<body>
<div class="w3-container">
  <div class="w3-panel w3-red w3-round">
    <h1>There was a problem</h1>
    <p><?php echo $error; ?></p>
  </div>
</div>
</body>
</html>
