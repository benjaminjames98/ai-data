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
else if ($q == 'read_payments') readPayments();
else if ($q == 'create_payment') createPayment();
else if ($q == 'read_payment') readPayment();
else if ($q == 'read_payees') readPayees();
else if ($q == 'create_payee') createPayee();
else if ($q == 'read_payee') readPayee();
else if ($q == 'update_payee') updatePayee();
else if ($q == 'read_students') readStudents();
else if ($q == 'read_receipts') readReceipts();
else if ($q == 'create_receipt') createReceipt();
else if ($q == 'read_receipt') readReceipt();
else if ($q == 'download_receipt') downloadReceipt();
else if ($q == 'read_invoices') readInvoices();
else if ($q == 'create_invoice') createInvoice();
else if ($q == 'read_invoice') readInvoice();
else if ($q == 'download_invoice') downloadInvoice();
else if ($q == 'read_contacts') readContacts();
else if ($q == 'create_contact') createContact();
else if ($q == 'read_contact') readContact();
else if ($q == 'update_contact') updateContact();
else if ($q == 'delete_contact') deleteContact();
else throwError('End Script');

function pwCheck() {
  echo json_encode(['pass' => true]);
  exit();
}

function readPayments() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-theme'>
    <td>date</td><td>description</td><td>amount</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;

  $query = "SELECT date_created, description, amount, __pk_id FROM payment ORDER BY `__pk_id` DESC";
  $result = $mysqli->query($query);
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $html .= <<<HTML
<tr>
  <td>{$row['date_created']}</td><td>{$row['description']}</td><td>{$row['amount']}</td>
  <td><button class='w3-button w3-padding-small w3-tiny' onclick="prepModal('mdl_pmt_red', {$row['__pk_id']})">&#9776</button></td>
</tr>
HTML;
    }
  else $html .= "<td></td><td></td><td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
  exit();
}

function createPayment() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $des = $arr[0];
  $amt = $arr[1];
  $gst = $arr[2];
  $query = "INSERT INTO payment (description, amount, gst, date_created) VALUES (?, ?, ?, CURDATE())";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("sss", $des, $amt, $gst);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_payment");
  $id = $mysqli->insert_id;;
  echo json_encode(['id' => $id]);
  exit();
}

function readPayment() {
  global $mysqli;
  $query = "SELECT __pk_id, date_created, description, amount, gst FROM payment WHERE __pk_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($num, $dat, $des, $amt, $gst);
  $stmt->fetch();
  $num = str_pad($num, 11, "0", STR_PAD_LEFT);

  echo json_encode(['num' => $num, 'dat' => $dat, 'des' => $des, 'amt' => $amt, 'gst' => $gst]);
  exit();
}

function readPayees() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-theme'>
    <td>id</td><td>name</td><td>overdue</td><td>students</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = <<<MYSQL
SELECT
  p.__pk_id AS id,
  p.name    AS name,
  GREATEST(IFNULL((SELECT MAX(DATEDIFF(CURDATE(), invoice.date_due))
   FROM invoice
   WHERE invoice.`_fk_payee` = p.`__pk_id` AND invoice.date_paid IS NULL), 0), 0) AS days
FROM payee AS p
GROUP BY p.`__pk_id`
ORDER BY p.`__pk_id`
MYSQL;
  $result = $mysqli->query($query);
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $idStr = str_pad($row['id'], 4, "0", STR_PAD_LEFT);
      $html .= "<tr><td>{$idStr}</td><td>{$row['name']}</td>";
      $html .= ($row['days'] > 30) ? "<td class='w3-text-red'>{$row['days']}</td>" : "<td>{$row['days']}</td>";
      $html .= "<td><button class='w3-button w3-padding-small w3-tiny' onclick=\"prepModal('mdl_pye_sdt', {$row['id']})\">&#x1F393</button></td>";
      $html .= "<td><button class='w3-button w3-padding-small w3-tiny' onclick=\"readPayee({$row['id']})\">&#9776</button></td></tr>";
    }
  else $html .= "<td></td><td></td><td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
  exit();
}

function createPayee() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $nam = $arr[0];
  $abn = $arr[1];
  $query = "INSERT INTO payee (name, abn) VALUES (?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ss", $nam, $abn);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_payee");
  $id = $mysqli->insert_id;
  echo json_encode(['id' => $id]);
  exit();
}

function readPayee() {
  global $mysqli;
  $id = $_GET['id'];
  $query = "SELECT  name,  abn,  balance,  `__pk_id`, unpaid
FROM payee
WHERE __pk_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($nam, $abn, $bal, $id, $upd);
  $stmt->fetch();
  $sav = <<<HTML
<button class='w3-btn w3-quarter w3-theme w3-mobile' onclick='updatePayee({$id})'>Save</button>
HTML;
  $ref = <<<HTML
<button class='w3-button w3-xlarge w3-right' title='refresh payee' onclick='readPayee({$id});'>&#8635;</button>
HTML;
  $rct = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick='createReceipt({$id});'>Create</button>
HTML;
  $ivc = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick='createInvoice({$id});'>Create</button>
HTML;
  $eml_crt = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick="createContact({$id}, 'eml');">Create</button>
HTML;
  $phn_crt = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick="createContact({$id}, 'phn');">Create</button>
HTML;
  $add_crt = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick="createContact({$id}, 'add');">Create</button>
HTML;
  echo json_encode(['nam' => $nam, 'abn' => $abn, 'bal' => $bal, 'sav' => $sav, 'rct' => $rct, 'id' => $id,
    'upd' => $upd, 'ref' => $ref, 'ivc' => $ivc, 'eml_crt' => $eml_crt, 'phn_crt' => $phn_crt,
    'add_crt' => $add_crt]);
  exit();
}

function updatePayee() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $nam = $arr[1];
  $abn = $arr[2];
  $query = "UPDATE payee SET name = ?, abn = ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ssi", $nam, $abn, $id);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("update_payee");
  echo json_encode(['id' => $id]);
  exit();
}

function readStudents() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable '>
  <thead>
  <tr class='w3-theme'>
    <td>id</td><td>name</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = "SELECT `__pk_id`, name FROM student WHERE `_fk_payee` = ? ORDER BY `__pk_id` ASC";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $nam);
    while ($stmt->fetch()) {
      $idStr = str_pad($id, 4, "0", STR_PAD_LEFT);
      $html .= "<tr><td>{$idStr}</td><td>{$nam}</td></tr>";
    }
  } else
    $html .= "<td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
}

function readReceipts() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-text-theme'>
    <td>id</td><td>description</td><td>amount</td><td>date</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = "SELECT `__pk_id`, description, amount, date_created FROM receipt WHERE `_fk_payee` = ? ORDER BY `__pk_id` DESC";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $des, $amt, $dat);
    while ($stmt->fetch()) {
      $idStr = str_pad($id, 11, "0", STR_PAD_LEFT);
      $html .= "<tr><td>{$idStr}</td><td>{$des}</td><td>{$amt}</td><td>{$dat}</td>";
      $html .= "<td><button class='w3-button w3-padding-small w3-tiny' onclick=\"prepModal('mdl_rct_red',{$id});\">&#9776</button></td></tr>";
    }
  } else
    $html .= "<td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
}

function createReceipt() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $pid = $arr[0];
  $des = $arr[1];
  $amt = $arr[2];
  $cor = ($arr[3] === 'yes') ? 1 : 0;
  $query = "INSERT INTO receipt (`_fk_payee`, description, amount, correction, date_created) VALUES (?,?,?,?, CURDATE())";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("isdi", $pid, $des, $amt, $cor);
  $stmt->execute();
  $rid = null;
  if ($stmt->affected_rows === 0) throwError("create_receipt_1");
  else {
    $rid = $mysqli->insert_id;
    generateReceipt($rid, $pid);
    $query = "UPDATE payee SET balance = balance + ? WHERE `__pk_id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $amt, $pid);
    $stmt->execute();
    payInvoices($pid);
  }
  echo json_encode(['rid' => $rid, 'pid' => $pid]);
  exit();
}

function readReceipt() {
  global $mysqli;
  $id = $_GET['id'];
  $query = "SELECT `__pk_id`, date_created, description, amount, correction FROM receipt WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($num, $dat, $des, $amt, $cor);
  $stmt->fetch();
  $num = str_pad($num, 11, "0", STR_PAD_LEFT);
  $dld = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick='downloadReceipt({$id})'>Download</button>
HTML;
  echo json_encode(['num' => $num, 'dat' => $dat, 'des' => $des, 'amt' => $amt, 'cor' => $cor, 'dld' => $dld]);
  exit();
}

function downloadReceipt() {
  $ridStr = str_pad($_GET['id'], 11, "0", STR_PAD_LEFT);
  $path = "../../../accounting_docs/rct" . $ridStr . ".html";

  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: public");
  header("Content-Description: File Transfer");
  header("Content-Type: application/octet-stream");
  header("Content-Length: " . (string)(filesize($path)));
  header('Content-Disposition: attachment; filename="' . basename($path) . '"');
  header("Content-Transfer-Encoding: binary\n");

  readfile($path);

  exit();
}

function readInvoices() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-text-theme'>
    <td>id</td><td>created</td><td>due</td><td>amount</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = <<<MYSQL
SELECT i.`__pk_id`,
  i.date_created,
  i.date_due,
  i.amount,
  IFNULL(i.date_paid, '')
FROM invoice AS i
WHERE i.`_fk_payee` = ?
ORDER BY i.`__pk_id` DESC
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $dat, $due, $amt, $paid);
    while ($stmt->fetch()) {
      $idStr = str_pad($id, 11, "0", STR_PAD_LEFT);
      $html .= "<tr><td>{$idStr}</td><td>{$dat}</td>";
      $html .= ($paid === '') ? "<td class='w3-text-theme''>{$due}</td>" : "<td>{$due}</td>";
      $html .= "<td>{$amt}</td>";
      $html .= "<td><button class='w3-button w3-padding-small w3-tiny' onclick=\"prepModal('mdl_ivc_red',{$id});\">&#9776</button></td></tr>";
    }
  } else
    $html .= "<td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
}

function createInvoice() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $pid = $arr[0];
  $due = $arr[2];
  $chgArr1 = explode("\n", $arr[1]);
  //des>qty>price>gst
  $chgArr2 = [];
  foreach ($chgArr1 as $chg) {
    $chgArr2[] = explode(">", $chg);
  }
  $stmt = null;
  $query = "INSERT INTO invoice (`_fk_payee`, date_created, date_due) VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $pid, $due);
  $stmt->execute();
  $iid = null;
  if ($stmt->affected_rows === 0) throwError("create_receipt_1");
  else {
    $iid = $mysqli->insert_id;
    $query = "INSERT INTO charge (`_fk_invoice`, description, quantity, amount, gst) VALUES (?,?,?,?,?)";
    $stmt = $mysqli->prepare($query);
    $ivcAmt = 0;
    $ivcGst = 0;
    foreach ($chgArr2 as $chg) {
      $ivcAmt += $chg[1] * $chg[2];
      $ivcGst += $chg[1] * $chg[3];
      $stmt->bind_param("issss", $iid, $chg[0], $chg[1], $chg[2], $chg[3]);
      $stmt->execute();
    }

    generateInvoice($iid, $pid);
    payInvoices($pid);
  }
  echo json_encode(['iid' => $iid, 'pid' => $pid]);
  exit();
}

function readInvoice() {
  global $mysqli;
  $id = $_GET['id'];
  $query = "SELECT `__pk_id`, amount FROM invoice WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($num, $amt);
  $stmt->fetch();
  $num = str_pad($num, 11, "0", STR_PAD_LEFT);
  $dld = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick='downloadInvoice({$id})'>Download</button>
HTML;
  echo json_encode(['num' => $num, 'amt' => $amt, 'dld' => $dld]);
  exit();
}

function downloadInvoice() {
  $iidStr = str_pad($_GET['id'], 11, "0", STR_PAD_LEFT);
  $path = "../../../accounting_docs/ivc" . $iidStr . ".html";

  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: public");
  header("Content-Description: File Transfer");
  header("Content-Type: application/octet-stream");
  header("Content-Length: " . (string)(filesize($path)));
  header('Content-Disposition: attachment; filename="' . basename($path) . '"');
  header("Content-Transfer-Encoding: binary\n");

  readfile($path);

  exit();
}

function readContacts() {
  global $mysqli;
  $pid = $_GET['id'];
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-text-theme'>
    <td>info</td><td>edit</td><td>delete</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = <<<MYSQL
SELECT
  'eml',
  `__pk_id`,
  email_address
FROM email
WHERE `_fk_payee` = ?
UNION ALL
SELECT
  'phn',
  `__pk_id`,
  phone_number
FROM phone
WHERE `_fk_payee` = ?
UNION ALL
SELECT
  'add',
  `__pk_id`,
  CONCAT(line_1, ", ", line_2, ", ", suburb, ", ", state, ", ", post_code)
FROM address
WHERE `_fk_payee` = ?
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("iii", $pid, $pid, $pid);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($typ, $id, $ctc);
    while ($stmt->fetch()) {
      $html .= <<<HTML
<tr>
  <td>{$ctc}</td>
  <td>
    <button class='w3-button w3-padding-small w3-tiny' onclick="prepModal('mdl_upt_ctc', '{$id}', '{$typ}')">&#9776</button>
  </td>
  <td>
    <button class='w3-button w3-padding-small w3-tiny' onclick="deleteContact('{$pid}', '{$id}', '{$typ}', '{$ctc}');">&#9776</button>
  </td>
</tr>
HTML;
    }
  } else
    $html .= "<td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
}

function createContact() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $type = $arr[0];
  $stmt = null;
  $msg = null;
  if ($type === 'eml') {
    $query = "INSERT INTO email (`_fk_payee`, email_address, type) VALUES (?, ?, 'payee')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id = $arr[1], $eml = $arr[2]);
    $msg = 'Email created: ' . $eml;
  } else if ($type === 'phn') {
    $query = "INSERT INTO phone (`_fk_payee`, phone_number, type) VALUES (?, ?, 'payee')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id = $arr[1], $phn = $arr[2]);
    $msg = 'Phone created: ' . $phn;
  } else if ($type === 'add') {
    $query = "INSERT INTO address (`_fk_payee`, line_1, line_2, suburb, state, post_code, type) VALUES (?,?,?,?,?,?, 'payee')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id = $arr[1], $ln1 = $arr[2], $ln2 = $arr[3], $sub = $arr[4],
      $stt = $arr[5], $pcd = $arr[6]);
    $msg = 'Address created: ' . $ln1 . ", " . $ln2 . ", " . $sub . ", " . $stt . ", " . $pcd;
  }
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_contact");
  echo json_encode(['msg' => $msg]);
  exit();
}

function readContact() {
  global $mysqli;
  $type = $_GET['type'];
  $id = $_GET['id'];
  $query = '';
  if ($type === 'eml') $query = "SELECT email_address, '', '', '', '', `_fk_payee` FROM email WHERE `__pk_id` = ?";
  else if ($type === 'phn') $query = "SELECT phone_number, '', '', '', '', `_fk_payee` FROM phone WHERE `__pk_id` = ?";
  else if ($type === 'add') $query = "SELECT line_1, line_2, suburb, state, post_code, `_fk_payee` FROM address WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($v0, $v1, $v2, $v3, $v4, $pid);
  $stmt->fetch();
  $udt = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick="updateContact({$pid},{$id},'{$type}');">Update</button>
HTML;
  echo json_encode(['v0' => $v0, 'v1' => $v1, 'v2' => $v2, 'v3' => $v3, 'v4' => $v4, 'udt' => $udt]);
  exit();
}

function updateContact() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $type = $arr[0];
  $stmt = null;
  $msg = null;
  if ($type === 'eml') {
    $query = "UPDATE email SET email_address = ? WHERE `__pk_id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("si", $eml = $arr[2], $cid = $arr[1]);
    $msg = 'Email updated: ' . $eml;
  } else if ($type === 'phn') {
    $query = "UPDATE phone SET phone_number = ? WHERE `__pk_id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("si", $phn = $arr[2], $cid = $arr[1]);
    $msg = 'Phone updated: ' . $phn;
  } else if ($type === 'add') {
    $query = "UPDATE address SET line_1 = ?,line_2 = ?,suburb = ?,state = ?,post_code = ? WHERE `__pk_id` = ?  ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssi", $ln1 = $arr[2], $ln2 = $arr[3], $sub = $arr[4],
      $stt = $arr[5], $pcd = $arr[6], $cid = $arr[1]);
    $msg = 'Address updated: ' . $ln1 . ", " . $ln2 . ", " . $sub . ", " . $stt . ", " . $pcd;
  }
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("update_contact");
  echo json_encode(['msg' => $msg]);
  exit();
}

function deleteContact() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $type = $arr[0];
  $query = null;
  if ($type === 'eml') $query = "DELETE FROM email WHERE `__pk_id` = ?";
  else if ($type === 'phn') $query = "DELETE FROM phone WHERE `__pk_id` = ?";
  else if ($type === 'add') $query = "DELETE FROM address WHERE `__pk_id` = ?";
  if (!isset($query)) throwError("delete_contact_1");
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id = $arr[1]);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("delete_contact_2");
  $msg = 'contact deleted';
  echo json_encode(['msg' => $msg]);
  exit();
}

function payInvoices($pid) {
  global $mysqli;
  $query = "CALL charge_payee_invoices(?);";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $pid);
  $stmt->execute();

  $query = <<<MYSQL
UPDATE payee
SET unpaid = (SELECT IFNULL(SUM(amount), 0)
              FROM invoice
              WHERE invoice.`_fk_payee` = payee.`__pk_id` AND invoice.date_paid IS NULL)
WHERE `__pk_id` = ?
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $pid);
  $stmt->execute();
}

function generateReceipt($rid, $pid) {
  global $mysqli;
  $rddStr = str_pad($rid, 11, "0", STR_PAD_LEFT);

  $query = <<<MYSQL
SELECT
  p.name,  p.balance - p.unpaid,  p.abn,
  DATE_FORMAT(r.date_created, '%e-%c-%Y'),
  r.amount, r.description
FROM payee AS p
  JOIN receipt AS r ON r.`_fk_payee` = p.`__pk_id`
WHERE r.`__pk_id` = ?
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $rid);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($p_nam, $p_bal, $p_abn, $r_crt, $r_amt, $r_des);
  $stmt->fetch();
  if ($p_abn === '') $p_abn = '-';

  $query = "SELECT line_1, line_2, suburb, state, post_code FROM address WHERE `_fk_payee` = ?;";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $pid);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($ln1, $ln2, $sub, $stt, $pcd);
  $stmt->fetch();
  $addLine1 = ($ln2 == '') ? $ln1 : $ln1 . ', ' . $ln2;
  $addLine2 = $sub . ', ' . $stt . ', ' . $pcd;

  $prevOwedVal = floatval(-$p_bal);
  $totalOwedVal = floatval(-$p_bal - $r_amt);
  $prevOwed = number_format($prevOwedVal, 2);
  $totalOwed = number_format($totalOwedVal, 2);

  $html = <<<HTML
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta content='text/html; charset=UTF-8'/>
  <title>Receipt {$rddStr}</title>
  <script src='finance.js'></script>
  <link rel='stylesheet' href='https://www.w3schools.com/w3css/4/w3.css'>
  <link rel='stylesheet' href='https://www.w3schools.com/lib/w3-theme-red.css'>
</head>
<body>
<div style='max-width: 210mm; margin-left: auto; margin-right: auto'>
  <header class='w3-section w3-container'>
    <div class='w3-row'>
      <h2 class='w3-left'>Antioch Initiative</h2>
      <h2 class='w3-right'>Receipt</h2>
    </div>
    <div class='w3-row'>
      <img style='width: 400px; height: 60px' class='w3-right w3-image' alt='antioch initiative logo'
          src='http://ai.org.au/accounting/media/antioch_initiative_logo.gif'/>
      <p>ABN: 24 107 171 399</p>
      <p>45 Tingira Rd</p>
      <p>Blackmans Bay, TAS, 7052</p>
    </div>
  </header>

  <section class='w3-section w3-container w3-row'>
    <div class='w3-left'>
      <p><b>Recipient</b></p>
      <p>ABN: {$p_abn}</p>
      <p>{$p_nam}</p>
      <p>{$addLine1}</p>
      <p>{$addLine2}</p>
    </div>
    <div class='w3-right'>
      <p><b>Receipt Num:</b> {$rddStr}</p>
      <p><b>Received:</b> {$r_crt}</p>
    </div>
  </section>

  <table class='w3-section w3-table w3-striped'>
    <thead>
    <tr class='w3-light-grey'>
      <td style='width: 40mm'><b>receipt number</b></td>
      <td><b>description</b></td>
      <td style='width: 25mm'><b></b></td>
      <td style='width: 25mm'><b></b></td>
      <td style='width: 25mm'><b>amount</b></td>
    </tr>
    </thead>
    <tfoot>
    <tr>
      <td></td>
      <td></td>
      <td colspan='2'><b>Sub Total:</b></td>
      <td>{$r_amt}</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td colspan='2'><b>Previous Owing:</b></td>
      <td>{$prevOwed}</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td colspan='2'><b>Total Owing:</b></td>
      <td>{$totalOwed}</td>
    </tr>
    </tfoot>
    <tbody>
    <tr>
      <td>{$rddStr}</td>
      <td style='column-span: 2'>{$r_des}</td>
      <td></td>
      <td></td>
      <td>{$r_amt}</td>
    </tr>
    </tbody>
  </table>
  <footer class='w3-container' style='page-break-inside:avoid;'>
    <div class='w3-section'>
      <p><b>Direct Deposit</b><br>BSB: 017526</br>Ac#: 465712585</br>Ref#: rct{$rddStr}</p>
    </div>
  </footer>
</div>
</body>
</html>
HTML;

  $fileLocation = "../../../accounting_docs/rct" . $rddStr . ".html";
  file_put_contents($fileLocation, $html);

  $query = "UPDATE receipt SET prev_owing = ?, total_owing = ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ddi", $prevOwedVal, $totalOwedVal, $rid);
  $stmt->execute();
}

function generateInvoice($iid, $pid) {
  global $mysqli;
  $iddStr = str_pad($iid, 11, "0", STR_PAD_LEFT);

  $query = <<<MYSQL
SELECT
  p.name,  p.balance - p.unpaid,  p.abn,
  DATE_FORMAT(i.date_created, '%e-%c-%Y'),
  DATE_FORMAT(i.date_due, '%e-%c-%Y')
FROM payee AS p
  JOIN invoice AS i ON i.`_fk_payee` = p.`__pk_id`
WHERE i.`__pk_id` = ?
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $iid);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($p_nam, $p_bal, $p_abn, $i_crt, $i_due);
  $stmt->fetch();
  if ($p_abn === '') $p_abn = '-';

  $query = "SELECT line_1, line_2, suburb, state, post_code FROM address WHERE `_fk_payee` = ?;";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $pid);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($ln1, $ln2, $sub, $stt, $pcd);
  $stmt->fetch();
  $addLine1 = ($ln2 == '') ? $ln1 : $ln1 . ', ' . $ln2;
  $addLine2 = $sub . ', ' . $stt . ', ' . $pcd;

  $query = "SELECT description, quantity, amount, gst FROM charge WHERE `_fk_invoice` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $iid);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($des, $qty, $amt, $gst);
  $htmlRows = '';
  $subTotal = 0;
  $gstTotal = 0;
  while ($stmt->fetch()) {
    $rowTotal = $amt * $qty;
    $subTotal += $rowTotal;
    $gstTotal += $gst * $qty;
    $amt = number_format($amt, 2);
    $rowTotal = number_format($rowTotal, 2);
    $htmlRows .= <<<HTML1
        <tr>
      <td>{$des}</td>
      <td>{$qty}</td>
      <td>{$amt}</td>
      <td>{$rowTotal}</td>
    </tr>
HTML1;
  }

  $subTotalVal = floatval($subTotal);
  $gstTotalVal = floatval($gstTotal);
  $prevOwedVal = floatval(-$p_bal);
  $totalOwedVal = floatval($subTotal - $p_bal);
  $subTotal = number_format($subTotalVal, 2);
  $gstTotal = number_format($gstTotalVal, 2);
  $prevOwed = number_format($prevOwedVal, 2);
  $totalOwed = number_format($totalOwedVal, 2);

  $html = <<<HTML
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta content='text/html; charset=UTF-8'/>
  <title>Invoice {$iddStr}</title>
  <script src='finance.js'></script>
  <link rel='stylesheet' href='https://www.w3schools.com/w3css/4/w3.css'>
  <link rel='stylesheet' href='https://www.w3schools.com/lib/w3-theme-red.css'>
</head>
<body>
<div style='max-width: 210mm; margin-left: auto; margin-right: auto'>
  <header class='w3-section w3-container'>
    <div class='w3-row'>
      <h2 class='w3-left'>Antioch Initiative</h2>
      <h2 class='w3-right'>Tax Invoice</h2>
    </div>
    <div class='w3-row'>
      <img style='width: 400px; height: 60px' class='w3-right w3-image' alt='antioch initiative logo'
          src='http://ai.org.au/accounting/media/antioch_initiative_logo.gif'/>
      <p>ABN: 24 107 171 399</p>
      <p>45 Tingira Rd</p>
      <p>Blackmans Bay, TAS, 7052</p>
    </div>
  </header>

  <section class='w3-section w3-container w3-row'>
    <div class='w3-left'>
      <p><b>Recipient</b></p>
      <p>ABN: {$p_abn}</p>
      <p>{$p_nam}</p>
      <p>{$addLine1}</p>
      <p>{$addLine2}</p>
    </div>
    <div class='w3-right'>
      <p><b>Invoice Num:</b> {$iddStr}</p>
      <p><b>Invoiced:</b> {$i_crt}</p>
      <p><b>Due:</b> {$i_due}</p>
    </div>
  </section>

  <table class='w3-section w3-table w3-striped'>
    <thead>
    <tr class='w3-light-grey'>
      <td><b>description</b></td>
      <td style='width: 30mm'><b>qty</b></td>
      <td style='width: 30mm'><b>unit price</b></td>
      <td style='width: 30mm'><b>price</b></td>
    </tr>
    </thead>
    <tfoot>
    <tr>
      <td></td>
      <td colspan='2'><b>Sub Total:</b></td>
      <td>{$subTotal}</td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'><b>GST(inc. GST):</b></td>
      <td>{$gstTotal}</td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'><b>Previous Owing:</b></td>
      <td>{$prevOwed}</td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'><b>Total Owing:</b></td>
      <td>{$totalOwed}</td>
    </tr>
    </tfoot>
    <tbody>
    {$htmlRows}
    </tbody>
  </table>

  <footer class='w3-container' style='page-break-inside:avoid;'>
    <div class='w3-section'>
      <p><b>Direct Deposit</b><br>BSB: 017526</br>Ac#: 465712585</br>Ref#: ivc{$iddStr}</p>
    </div>
  </footer>
</div>
</body>
</html>
HTML;

  $query = <<<MYSQL
UPDATE invoice
SET amount = ?, gst = ?, prev_owing = ?, total_owing = ?
WHERE `__pk_id` = ?
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ddddi", $subTotalVal, $gstTotalVal, $prevOwedVal, $totalOwedVal, $iid);
  $stmt->execute();

  $fileLocation = "../../../accounting_docs/ivc" . $iddStr . ".html";
  file_put_contents($fileLocation, $html);
}