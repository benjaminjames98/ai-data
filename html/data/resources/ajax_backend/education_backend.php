<?php
header("Content-Type: application/json; charset=UTF-8");

require "../../../../includes/db_connect.php";

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
elseif ($_SERVER['REQUEST_METHOD'] == 'GET')
  $q = $_GET['q'];
else throwError('Request Method');

/*---- $q ----*/

if ($q == 'pw_check') pwCheck();
elseif ($q == 'read_students') readStudents();
elseif ($q == 'create_student') createStudent();
elseif ($q == 'update_student_info') updateStudentInfo();
elseif ($q == 'update_student_payee') updateStudentPayee();
elseif ($q == 'read_student') readStudent();
elseif ($q == 'read_cohorts') readCohorts();
elseif ($q == 'create_cohort') createCohort();
elseif ($q == 'update_cohort_info') updateCohortInfo();
elseif ($q == 'update_cohort_note') updateCohortNote();
elseif ($q == 'read_cohort') readCohort();
elseif ($q == 'read_cohort_students') readCohortStudents();
elseif ($q == 'create_cohort_student') createCohortStudent();
elseif ($q == 'delete_cohort_student') deleteCohortStudent();
// Contacts
elseif ($q == 'read_contacts') readContacts();
elseif ($q == 'create_contact') createContact();
elseif ($q == 'read_contact') readContact();
elseif ($q == 'update_contact') updateContact();
elseif ($q == 'delete_contact') deleteContact();
// Misc
elseif ($q == 'read_payees') readPayees();
elseif ($q == 'cohort_prep') cohortPrep();
else throwError('End Script');


function pwCheck() {
  echo json_encode(['pass' => true]);
  exit();
}

function readStudents() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-theme'>
    <td>ID</td><td style='white-space: nowrap; width: 100%;'>name</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;

  $query = "SELECT __pk_id, name FROM student ORDER BY `__pk_id` ASC";
  $result = $mysqli->query($query);
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $html .= <<<HTML
<tr>
  <td>{$row['__pk_id']}</td><td>{$row['name']}</td>
  <td><button class='w3-button w3-padding-small w3-tiny' onclick='readStudent({$row['__pk_id']})'>&#9776</button></td>
</tr>
HTML;
    }
  else $html .= "<td></td><td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
  exit();
}

function createStudent() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $nam = $arr[0];
  $pye = $arr[1];
  $query = "INSERT INTO student (name, `_fk_payee`) VALUES (?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("si", $nam, $pye);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_student");
  $id = $mysqli->insert_id;
  echo json_encode(['id' => $id]);
  exit();
}

function readStudent() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $id = $arr[0];
  $query =
    "SELECT `__pk_id`, `_fk_payee`, name, notes FROM student WHERE __pk_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($id, $pid, $nam, $not);
  $stmt->fetch();
  if ($not == null) $not = '';
  $sav = <<<HTML
<button class='w3-btn w3-theme w3-section' title='save student details' onclick='updateStudentInfo({$id})'>Save</button>
HTML;
  $chg = <<<HTML
<button class='w3-btn w3-theme w3-section' title='save student details' onclick='updateStudentPayee({$id})'>Change</button>
HTML;
  $ref = <<<HTML
<button class='w3-button w3-xlarge w3-right' title='refresh student' onclick='readStudent({$id});'>&#8635;</button>
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
  echo json_encode(['id'      => $id, 'pid' => $pid, 'nam' => $nam,
                    'not'     => $not, 'sav' => $sav, 'chg' => $chg,
                    'ref'     => $ref, 'eml_crt' => $eml_crt,
                    'phn_crt' => $phn_crt, 'add_crt' => $add_crt]);
  exit();
}

function updateStudentInfo() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $nam = $arr[1];
  $not = $arr[2];
  $query = "UPDATE student SET name = ?, notes = ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ssi", $nam, $not, $id);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("update_student_info");
  echo json_encode(['id' => $id]);
  exit();
}

function updateStudentPayee() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $pid = $arr[1];
  $query = "UPDATE student SET `_fk_payee` = ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $pid, $id);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("update_student_payee");
  echo json_encode(['id' => $id]);
  exit();
}

function readCohorts() {
  global $mysqli;
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-theme'>
    <td>ID</td><td style='white-space: nowrap; width: 100%;'>name</td><td>view</td>
  </tr>
  </thead>
  <tbody>
HTML;

  $query = "SELECT __pk_id, name FROM cohort ORDER BY `__pk_id` ASC";
  $result = $mysqli->query($query);
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $html .= <<<HTML
<tr>
  <td>{$row['__pk_id']}</td><td>{$row['name']}</td>
  <td><button class='w3-button w3-padding-small w3-tiny' onclick='readCohort({$row['__pk_id']})'>&#9776</button></td>
</tr>
HTML;
    }
  else $html .= "<td></td><td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
  exit();
}

function createCohort() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $nam = $arr[0];
  $ldr = $arr[1];
  $umb = $arr[2];
  $atv = $arr[3];
  $cbk = $arr[4];
  $nbk = $arr[5];
  $pgm = $arr[6];
  $query =
    "INSERT INTO cohort (name, `_fk_leader`, unlisted_members, active, `_fk_current_book`, `_fk_next_book`, `_fk_program`) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("siiiiii", $nam, $ldr, $umb, intval($atv == 'true'), $cbk,
    $nbk, $pgm);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_cohort");
  $id = $mysqli->insert_id;
  echo json_encode(['id' => $id]);
  exit();
}

function updateCohortInfo() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $nam = $arr[1];
  $ldr = $arr[2];
  $umb = $arr[3];
  $atv = $arr[4];
  $cbk = $arr[5];
  $nbk = $arr[6];
  $pgm = $arr[7];
  $query = <<<MYSQL
UPDATE cohort 
SET name = ?, `_fk_leader` = ?, unlisted_members = ?, active = ?, `_fk_current_book` = ?, `_fk_next_book` = ?, `_fk_program` = ?
WHERE `__pk_id` = ?;
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("siiiiiii", $nam, $ldr, $umb, intval($atv == 'true'), $cbk,
    $nbk, $pgm, $id);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("update_cohort_info");
  $id = $mysqli->insert_id;
  echo json_encode(['id' => $id]);
  exit();
}

function updateCohortNote() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $id = $arr[0];
  $nam = $arr[1];
  $not = $arr[2];
  $query = "UPDATE cohort SET name = ?, notes = ? WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ssi", $nam, $not, $id);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("update_cohort_note");
  echo json_encode(['id' => $id]);
  exit();
}

function readCohort() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $id = $arr[0];
  $query = <<<MYSQL
SELECT
  `__pk_id`,
  name,
  notes,
  unlisted_members,
  active,
  `_fk_leader`,
  `_fk_current_book`,
  `_fk_next_book`,
  `_fk_program`
FROM cohort
WHERE __pk_id = ?
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($id, $nam, $not, $umb, $atv, $ldr, $cbk, $nbk, $pgm);
  $stmt->fetch();
  if ($not == null) $not = '';
  $ref = <<<HTML
<button class='w3-button w3-xlarge w3-right' title='refresh student' onclick='readCohort({$id});'>&#8635;</button>
HTML;
  $sav = <<<HTML
<button class='w3-btn w3-theme w3-section' title='save cohort info' onclick='updateCohortInfo({$id})'>Save</button>
HTML;
  $chg = <<<HTML
<button class='w3-btn w3-theme w3-section' title='save cohort note' onclick='updateCohortNote({$id})'>Change</button>
HTML;
  $sdt = <<<HTML
<button class='w3-btn w3-theme w3-right w3-margin-left' onclick='createCohortStudent({$id});'>Add</button>
HTML;


  echo json_encode(['id'  => $id, 'nam' => $nam, 'not' => $not, 'umb' => $umb,
                    'atv' => $atv, 'ldr' => $ldr, 'cbk' => $cbk,
                    'nbk' => $nbk, 'pgm' => $pgm, 'ref' => $ref, 'sav' => $sav,
                    'chg' => $chg, 'sdt' => $sdt]);
  exit();
}

function readCohortStudents() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $cid = $arr[0];
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-text-theme'>
    <td style='white-space: nowrap; width: 100%;'>name</td><td>delete</td>
  </tr>
  </thead>
  <tbody>
HTML;
  $query = <<<MYSQL
SELECT s.name, e.`__pk_id`
FROM enrollment AS e, student AS s
WHERE s.`__pk_id` = e._fk_student AND e.`_fk_cohort` = ?
ORDER BY s.name ASC;
MYSQL;
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $cid);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($nam, $id);
    while ($stmt->fetch()) {
      $html .= <<<HTML
<tr>
  <td>{$nam}</td>
  <td>
    <button class='w3-button w3-padding-small w3-tiny' onclick="deleteCohortStudent({$id}, '{$nam}', {$cid});">&times;</button>
  </td>
</tr>
HTML;
    }
  } else
    $html .= "<td></td><td></td>";
  $html .= "</tbody></table>";
  echo json_encode(['html' => $html]);
}

function createCohortStudent() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $cid = $arr[0];
  $pid = $arr[1];
  $query = "INSERT INTO enrollment (`_fk_cohort`, `_fk_student`) VALUES (?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $cid, $pid);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_cohort_student");
  echo json_encode(['cid' => $cid]);
  exit();
}

function deleteCohortStudent() {
  global $mysqli;
  $arr = explode('||', $_POST['arr']);
  $eid = $arr[0];
  $query = "DELETE FROM enrollment WHERE `__pk_id` = ?";
  if (!isset($query)) throwError("delete_cohort_student_1");
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $eid);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("delete_cohort_student_2");
  echo json_encode(['eid' => $eid]);
  exit();
}

// Contacts
function readContacts() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $pid = $arr[0];
  $html = <<<HTML
<table class='w3-table-all w3-hoverable w3-border-0'>
  <thead>
  <tr class='w3-text-theme'>
    <td style='white-space: nowrap; width: 100%;'>info</td><td>edit</td><td>delete</td>
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
WHERE `_fk_student` = ?
UNION ALL
SELECT
  'phn',
  `__pk_id`,
  phone_number
FROM phone
WHERE `_fk_student` = ?
UNION ALL
SELECT
  'add',
  `__pk_id`,
  CONCAT(line_1, ", ", line_2, ", ", suburb, ", ", state, ", ", post_code)
FROM address
WHERE `_fk_student` = ?
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
    $query =
      "INSERT INTO email (`_fk_student`, email_address, type) VALUES (?, ?, 'student')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id = $arr[1], $eml = $arr[2]);
    $msg = 'Email created: ' . $eml;
  } elseif ($type === 'phn') {
    $query =
      "INSERT INTO phone (`_fk_student`, phone_number, type) VALUES (?, ?, 'student')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $id = $arr[1], $phn = $arr[2]);
    $msg = 'Phone created: ' . $phn;
  } elseif ($type === 'add') {
    $query =
      "INSERT INTO address (`_fk_student`, line_1, line_2, suburb, state, post_code, type) VALUES (?,?,?,?,?,?, 'student')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $id = $arr[1], $ln1 = $arr[2], $ln2 = $arr[3],
      $sub = $arr[4],
      $stt = $arr[5], $pcd = $arr[6]);
    $msg = 'Address created: ' . $ln1 . ", " . $ln2 . ", " . $sub . ", " . $stt
      . ", " . $pcd;
  }
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("create_contact");
  echo json_encode(['msg' => $msg]);
  exit();
}

function readContact() {
  global $mysqli;
  $arr = explode('||', $_GET['arr']);
  $id = $arr[0];
  $type = $arr[1];
  $query = '';
  if ($type === 'eml') $query =
    "SELECT email_address, '', '', '', '', `_fk_student` FROM email WHERE `__pk_id` = ?";
  elseif ($type === 'phn') $query =
    "SELECT phone_number, '', '', '', '', `_fk_student` FROM phone WHERE `__pk_id` = ?";
  elseif ($type === 'add') $query =
    "SELECT line_1, line_2, suburb, state, post_code, `_fk_student` FROM address WHERE `__pk_id` = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($v0, $v1, $v2, $v3, $v4, $pid);
  $stmt->fetch();
  $udt = <<<HTML
<button class='w3-btn w3-theme w3-right' onclick="updateContact({$pid},{$id},'{$type}');">Update</button>
HTML;
  echo json_encode(['v0' => $v0, 'v1' => $v1, 'v2' => $v2, 'v3' => $v3,
                    'v4' => $v4, 'udt' => $udt]);
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
  } elseif ($type === 'phn') {
    $query = "UPDATE phone SET phone_number = ? WHERE `__pk_id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("si", $phn = $arr[2], $cid = $arr[1]);
    $msg = 'Phone updated: ' . $phn;
  } elseif ($type === 'add') {
    $query =
      "UPDATE address SET line_1 = ?,line_2 = ?,suburb = ?,state = ?,post_code = ? WHERE `__pk_id` = ?  ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssi", $ln1 = $arr[2], $ln2 = $arr[3], $sub = $arr[4],
      $stt = $arr[5], $pcd = $arr[6], $cid = $arr[1]);
    $msg = 'Address updated: ' . $ln1 . ", " . $ln2 . ", " . $sub . ", " . $stt
      . ", " . $pcd;
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
  elseif ($type === 'phn') $query = "DELETE FROM phone WHERE `__pk_id` = ?";
  elseif ($type === 'add') $query = "DELETE FROM address WHERE `__pk_id` = ?";
  if (!isset($query)) throwError("delete_contact_1");
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $id = $arr[1]);
  $stmt->execute();
  if ($stmt->affected_rows === 0) throwError("delete_contact_2");
  $msg = 'contact deleted';
  echo json_encode(['msg' => $msg]);
  exit();
}

// Misc
function readPayees() {
  global $mysqli;
  $query = "SELECT `__pk_id`, name FROM payee;";
  $result = $mysqli->query($query);
  $html = "";
  if ($result->num_rows > 0)
    while ($row = $result->fetch_assoc()) {
      $name = $row['__pk_id'] . " " . $row['name'];
      $html .= "<option value='{$row['__pk_id']}'>$name</option>";
    }
  else $html .= "";
  echo json_encode(['html' => $html]);
  exit();
}

function cohortPrep() {
  global $mysqli;

  $query = "SELECT `__pk_id`, name FROM student;";
  $result0 = $mysqli->query($query);
  $ldrs = "";
  if ($result0->num_rows > 0)
    while ($row = $result0->fetch_assoc()) {
      $name = $row['__pk_id'] . " " . $row['name'];
      $ldrs .= "<option value='{$row['__pk_id']}'>$name</option>";
    }
  else $ldrs .= "";

  $query = "SELECT `__pk_id`, name FROM book;";
  $result1 = $mysqli->query($query);
  $boks = "";
  if ($result1->num_rows > 0)
    while ($row = $result1->fetch_assoc()) {
      $name = $row['__pk_id'] . " " . $row['name'];
      $boks .= "<option value='{$row['__pk_id']}'>$name</option>";
    }
  else $boks .= "";

  $query = "SELECT `__pk_id`, name FROM program;";
  $result2 = $mysqli->query($query);
  $pgms = "";
  if ($result2->num_rows > 0)
    while ($row = $result2->fetch_assoc()) {
      $name = $row['__pk_id'] . " " . $row['name'];
      $pgms .= "<option value='{$row['__pk_id']}'>$name</option>";
    }
  else $pgms .= "";

  echo json_encode(['ldrs' => $ldrs, 'boks' => $boks, 'pgms' => $pgms]);
  exit();
}