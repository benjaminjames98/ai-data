<?php
header("Content-Type: application/json; charset=UTF-8");

require "../includes/db_connect.php";

function throwError($msg = null) {
  echo json_encode(["a" => "0", "msg" => "error in: " . $msg]);
  exit();
}

$mysqli = getMysqli();
if ($mysqli == -1) throwError();

/*---- PREPERATION ----*/

$q = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
  $q = $_POST['q'];
else if ($_SERVER['REQUEST_METHOD'] == 'GET')
  $q = $_GET['q'];
else throwError('Request Method');

/*---- $q ----*/

if ($q == 'pw_check') pwCheck();
if ($q == 'read_books') readBooks();
if ($q == 'create_book') createBook();
if ($q == 'read_book') readBook();
if ($q == 'update_book') updateBook();
if ($q == 'read_stock') readStock();
if ($q == 'update_stock') updateStock();
if ($q == 'read_programs') readPrograms();
if ($q == 'create_program') createProgram();
if ($q == 'download_document') downloadDocument();
else throwError('End Script');

function pwCheck() {
  echo json_encode(['pass' => true]);
  exit();
}

function readBooks() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-theme'>
    <td>id</td><td>name</td><td>use</td><td>stock</td><td>demand</td><td>price</td><td>stock</td><td>edit</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = "SELECT `__pk_id`,name, 'in use' AS 'use', stock, 'demand' AS demand, price FROM book ORDER BY `__pk_id` ASC";
  $result = $mysqli->query($query);
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $html .= <<<HTML
<tr>
  <td>{$row['__pk_id']}</td>
  <td>{$row['name']}</td>
  <td>{$row['use']}</td>
  <td>{$row['stock']}</td>
  <td>{$row['demand']}</td>
  <td>{$row['price']}</td>
  <td><button class='w3-button w3-padding-small w3-tiny' onclick="prepModal('mdl_stk_udt', {$row['__pk_id']})">&#9776</button></td>
  <td><button class='w3-button w3-padding-small w3-tiny' onclick="prepModal('mdl_bok_udt', {$row['__pk_id']})">&#9776</button></td>
  <td><button class='w3-button w3-padding-small w3-tiny' onclick="prepModal('mdl_bok_red', {$row['__pk_id']})">&#9776</button></td>
</tr>
HTML;
    }
  else $html .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
  exit();
}

function createBook() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $nam = $arr[0];
  $des = $arr[1];
  $prc = $arr[2];
  $query = "INSERT INTO book (name, description, price, stock) VALUES (?, ?, ?, 0)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("sss", $nam, $des, $prc);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_book");
  $id = $mysqli->insert_id;;
  echo json_encode(['id' => $id]);
  exit();
}

function readBook() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $id = $arr[0];
  $query = "SELECT name, description, price FROM book WHERE __pk_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($nam, $des, $prc);
  $stmt->fetch();
  $udt = "<button class='w3-btn w3-theme w3-right' onclick='updateBook({$id})'>Update</button>";
  echo json_encode(['nam' => $nam, 'des' => $des, 'prc' => $prc, 'udt' => $udt]);
  exit();
}

function updateBook() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $nam = $arr[1];
  $des = $arr[2];
  $prc = $arr[3];
  $query = "UPDATE book SET name = ?, description = ?, price = ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("sssi", $nam, $des, $prc, $id);
  $stmt->execute();
  echo json_encode(['id' => $id]);
  exit();
}

function readStock() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $id = $arr[0];
  $query = "SELECT stock FROM book WHERE __pk_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($stk);
  $stmt->fetch();
  $udt = "<button class='w3-btn w3-theme w3-right' onclick='updateStock({$id})'>Increment</button>";
  echo json_encode(['stk' => $stk, 'udt' => $udt]);
  exit();
}

function updateStock() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $stk = $arr[1];
  $query = "UPDATE book SET stock = stock + ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("si", $stk, $id);
  $stmt->execute();
  echo json_encode(['id' => $id]);
  exit();
}

function readPrograms() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-theme'>
    <td>name</td><td>description</td><td>enrolled</td>
  </tr>
  </thead>
  <tbody>
HTML;

  $query = "SELECT name, description, 'enrolled' AS enrolled FROM program ORDER BY `__pk_id` ASC";
  $result = $mysqli->query($query);
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $html .= "<tr><td>{$row['name']}</td><td>{$row['description']}</td><td>{$row['enrolled']}</td></tr>";
    }
  else $html .= "<td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
  exit();
}

function createProgram() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $nam = $arr[0];
  $des = $arr[1];
  $query = "INSERT INTO program (name, description) VALUES (?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ss", $nam, $des);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_program");
  $id = $mysqli->insert_id;;
  echo json_encode(['id' => $id]);
  exit();
}

function downloadDocument() {
  global $mysqli;
  $con = explode('||', $_GET['con']);
  $dat = $_GET['dat'];
  $nam = $_GET['nam'];
  $top = $_GET['top'];
  $bot = $_GET['bot'];

  $query = 'SELECT name FROM book WHERE `__pk_id` = ?';
  $stmt = $mysqli->prepare($query);
  $tableRows = '';
  foreach ($con as $c) {
    $row = explode('>', $c);
    $stmt->bind_param("i", $row[0]);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $tableRows .= "<tr></tr><td>{$name}</td><td>$row[1]</td><td>&#9744;</><td>&#9744;</td></tr>";
  }

  $html = <<<HTML
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta content='text/html; charset=UTF-8'/>
  <title>Dispatch Document</title>
  <link rel='stylesheet' href='https://www.w3schools.com/w3css/4/w3.css'>
  <link rel='stylesheet' href='https://www.w3schools.com/lib/w3-theme-red.css'>
</head>
<body>
<div style='max-width: 210mm; margin-left: auto; margin-right: auto'>
  <header class='w3-section w3-container'>
    <div class='w3-row'>
      <h2 class='w3-left'>CityNetworks</h2>
      <h2 class='w3-right'>Dispatch Document</h2>
    </div>
    <div class='w3-row'>
      <img style='width: 200px; height: 100px' class='w3-right w3-image' alt='citynetworks logo'
          src='http://ai.org.au/accounting/media/city_networks_logo.png'/>
      <p>ABN: 24 107 171 399</p>
      <p>45 Tingira Rd</p>
      <p>Blackmans Bay, TAS, 7052</p>
    </div>
  </header>

  <section class='w3-section w3-container w3-row'>
    <div class='w3-left'>
      <p><b>Recipient</b></p>
      <p>{$nam}</p>
      <p>{$top}</p>
      <p>{$bot}</p>
    </div>
    <div class='w3-right'>
      <p><b>Ordered:</b> {$dat}</p>
    </div>
  </section>

  <table class='w3-section w3-table w3-striped'>
    <thead>
    <tr class='w3-light-grey'>
      <td><b>name</b></td>
      <td style='width: 15mm'><b>qty</b></td>
      <td style='width: 10mm'><b>ivc</b></td>
      <td style='width: 15mm'><b>sent</b></td>
    </tr>
    </thead>
    <tbody>
    {$tableRows}
    </tbody>
  </table>
</div>
</body>
</html>
HTML;

  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: public");
  header("Content-Description: File Transfer");
  header("Content-Type: application/octet-stream");
  header("Content-Transfer-Encoding: binary\n");

  echo $html;
}