<?php
include_once "../../../includes/db_connect.php";

function throwError($msg = '') {
  die(json_encode(["a" => "0", "msg" => "error in: " . $msg]));
}

if (!array_key_exists('q', $_REQUEST)) throwError('determining request code');
if (($db = getMysqli()) == -1) throwError('accessing DB');
$q = $_REQUEST['q'];
/*---- Prep ----*/

if ($q == 'read_db') readDb();
if ($q == 'create_church') createChurch();
if ($q == 'read_church') readChurch();
if ($q == 'update_church') updateChurch();
if ($q == 'create_person') createPerson();
if ($q == 'read_person') readPerson();
if ($q == 'update_person') updatePerson();
if ($q == 'create_contact') createContact();
if ($q == 'update_contact') updateContact();
if ($q == 'delete_contact') deleteContact();
if ($q == 'create_role') createRole();
if ($q == 'update_role') updateRole();
if ($q == 'delete_role') deleteRole();
else throwError('End Script');

function readDB() {

  function readChurches() {
    global $db;
    $query =
      "SELECT `__pk_id`, name, denomination, `_fk_area`, visibility, note  FROM church";
    if (!$stmt = $db->prepare($query)) throwError("read_church");
    $stmt->execute();
    $cid = $nam = $den = $reg = $vis = $not = null;
    $stmt->bind_result($cid, $nam, $den, $reg, $vis, $not);
    $churches = [];
    while ($stmt->fetch())
      $churches[] =
        ['id' => $cid, 'name' => $nam, 'denomination' => $den,
          'region' => $reg, 'visibility' => $vis, 'note' => $not];
    $stmt->close();

    for ($i = 0; $i < count($churches); $i++) {
      $churches[$i]['contacts'] = readContacts('church', $churches[$i]['id']);
      $churches[$i]['people'] = readRoles('church', $churches[$i]['id']);
    }

    return $churches;
  }

  function readPeople() {
    global $db;
    $query =
      "SELECT `__pk_id`, first_name, last_name, CONCAT(first_name, ' ', last_name), note"
      . " FROM person";
    if (!$stmt = $db->prepare($query)) throwError("read_people");
    $stmt->execute();
    $pid = $fnm = $lnm = $nam = $not = null;
    $stmt->bind_result($pid, $fnm, $lnm, $nam, $not);
    $people = [];
    while ($stmt->fetch())
      $people[] = ['id' => $pid, 'first_name' => $fnm, 'last_name' => $lnm,
        'name' => $nam, 'note' => $not];
    $stmt->close();

    for ($i = 0; $i < count($people); $i++) {
      $people[$i]['contacts'] = readContacts('person', $people[$i]['id']);
      $people[$i]['churches'] = readRoles('person', $people[$i]['id']);
    }

    return $people;
  }

  $arr = ['churches' => readChurches(), 'people' => readPeople()];
  die(json_encode($arr));
}

function createChurch() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('create_church 1');
  $data = json_decode($_REQUEST['data']);

  $query =
    "INSERT INTO church (name, denomination, `_fk_area`, visibility) VALUES (?, ?, ?, ?)";
  if (!$stmt = $db->prepare($query)) throwError("create_church 2");
  $stmt->bind_param('ssss', $data->name, $data->denomination,
    $data->region, $data->visibility);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  if (!$success) die(json_encode(['success' => $success]));

  $query = "SELECT LAST_INSERT_ID()";
  if (!$stmt = $db->prepare($query)) throwError("create_church 3");
  $stmt->execute();
  $church_id = null;
  $stmt->bind_result($church_id);
  $stmt->fetch();

  die(json_encode(['success' => $success, 'church_id' => $church_id]));
}

function readChurch() {
  global $db;
  if (!array_key_exists('id', $_REQUEST)) throwError('read_church 1');
  $id = $_REQUEST['id'];

  $query =
    "SELECT `__pk_id`, name, denomination, `_fk_area`, visibility, note  FROM church WHERE `__pk_id` = ?";
  if (!$stmt = $db->prepare($query)) throwError("read_church");
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $cid = $nam = $den = $reg = $vis = $not = null;
  $stmt->bind_result($cid, $nam, $den, $reg, $vis, $not);
  $stmt->fetch();
  $church =
    ['id' => $cid, 'name' => $nam, 'denomination' => $den,
      'region' => $reg, 'visibility' => $vis, 'note' => $not];
  $stmt->close();

  $church['contacts'] = readContacts('church', $cid);
  $church['people'] = readRoles('church', $cid);

  die(json_encode(['church' => $church]));
}

function updateChurch() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('update_church 1');
  $ch = json_decode($_REQUEST['data']);

  $query =
    "UPDATE church SET name=?, denomination=?, `_fk_area`=?, visibility=?, note=? WHERE `__pk_id`=?";
  if (!$stmt = $db->prepare($query)) throwError("update_church 2");
  $stmt->bind_param('ssssss', $ch->name, $ch->denomination, $ch->region,
    $ch->visibility, $ch->note, $ch->id);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function createPerson() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('create_person 1');
  $data = json_decode($_REQUEST['data']);

  $query = "INSERT INTO person (first_name, last_name) VALUES (?, ?)";
  if (!$stmt = $db->prepare($query)) throwError("create_person 2");
  $stmt->bind_param('ss', $data->first_name, $data->last_name);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  if (!$success) die(json_encode(['success' => $success]));

  $query = "SELECT LAST_INSERT_ID()";
  if (!$stmt = $db->prepare($query)) throwError("create_person 3");
  $stmt->execute();
  $person_id = null;
  $stmt->bind_result($person_id);
  $stmt->fetch();

  die(json_encode(['success' => $success, 'person_id' => $person_id]));
}

function updatePerson() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('update_person 1');
  $ch = json_decode($_REQUEST['data']);

  $query =
    "UPDATE person SET first_name=?, last_name=?, note=? WHERE `__pk_id`=?";
  if (!$stmt = $db->prepare($query)) throwError("update_person 2");
  $stmt->bind_param('ssss', $ch->first_name, $ch->last_name, $ch->note,
    $ch->id);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function readPerson() {
  global $db;
  if (!array_key_exists('id', $_REQUEST)) throwError('read_person 1');
  $id = $_REQUEST['id'];

  $query =
    "SELECT first_name, last_name, CONCAT(first_name, ' ', last_name), note"
    . " FROM person where __pk_id=?";
  if (!$stmt = $db->prepare($query)) throwError("read_people");
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $fnm = $lnm = $nam = $not = null;
  $stmt->bind_result($fnm, $lnm, $nam, $not);
  $stmt->fetch();
  $person =
    ['id' => $id, 'first_name' => $fnm, 'last_name' => $lnm, 'name' => $nam,
      'note' => $not];
  $stmt->close();

  $person['contacts'] = readContacts('person', $id);
  $person['churches'] = readRoles('person', $id);

  die(json_encode(['person' => $person]));
}

function createContact() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('create_contact 1');
  $data = json_decode($_REQUEST['data']);
  $fk = $data->cp === 'p' ? '_fk_person' : '_fk_church';


  $query_array = [
    'web' => "INSERT INTO website (${fk}, url, type) VALUES (?, ?, 'primary')",
    'eml' => "INSERT INTO email (${fk}, email_address, type) VALUES (?, ?, 'primary')",
    'phn' => "INSERT INTO phone (${fk}, phone_number, type) VALUES (?, ?, 'primary')",
    'add' => "INSERT INTO address (${fk}, line_1, line_2, suburb, state, post_code, type)"
      . " VALUES (?, ?, ?, ?, ?, ?, 'primary')"
  ];
  $query = $query_array[$data->type];
  if (!$stmt = $db->prepare($query)) throwError("create_contact 2");
  if ($data->type === 'web') $stmt->bind_param('ss', $data->id, $data->url);
  if ($data->type === 'eml') $stmt->bind_param('ss', $data->id, $data->eml);
  if ($data->type === 'phn') $stmt->bind_param('ss', $data->id, $data->phn);
  if ($data->type === 'add') $stmt->bind_param('ssssss', $data->id, $data->ln1,
    $data->ln2, $data->sub, $data->stt, $data->pcd);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function updateContact() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('create_contact 1');
  $data = json_decode($_REQUEST['data']);

  $query_array = [
    'web' => "UPDATE website SET url=? WHERE `__pk_id`=?",
    'eml' => "UPDATE email SET email_address=? WHERE `__pk_id`=?",
    'phn' => "UPDATE phone SET phone_number=? WHERE `__pk_id`=?",
    'add' => "UPDATE address SET line_1=?, line_2=?, suburb=?, state=?, post_code=?"
      . " WHERE `__pk_id`=?"
  ];
  $query = $query_array[$data->type];
  if (!$stmt = $db->prepare($query)) throwError("create_contact 2");
  if ($data->type === 'web') $stmt->bind_param('ss', $data->url, $data->id);
  if ($data->type === 'eml') $stmt->bind_param('ss', $data->eml, $data->id);
  if ($data->type === 'phn') $stmt->bind_param('ss', $data->phn, $data->id);
  if ($data->type === 'add') $stmt->bind_param('ssssss', $data->ln1,
    $data->ln2, $data->sub, $data->stt, $data->pcd, $data->id);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function deleteContact() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('delete_contact 1');
  $data = json_decode($_REQUEST['data']);

  $query_array = [
    'web' => 'DELETE FROM website WHERE `__pk_id` = ?',
    'eml' => 'DELETE FROM email WHERE `__pk_id` = ?',
    'phn' => 'DELETE FROM phone WHERE `__pk_id` = ?',
    'add' => 'DELETE FROM address WHERE `__pk_id` = ?'
  ];
  $query = $query_array[$data->type];
  if (!$stmt = $db->prepare($query)) throwError("create_contact 2");
  $stmt->bind_param('s', $data->contact_id);
  $stmt->execute();
  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function createRole() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('create_role 1');
  $data = json_decode($_REQUEST['data']);

  $query =
    "INSERT INTO role (`_fk_person`, `_fk_church`, type) VALUES (?, ?, ?)";
  if (!$stmt = $db->prepare($query)) throwError("create_role 2");
  $stmt->bind_param('sss', $data->person_id, $data->church_id, $data->type);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function updateRole() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('create_role 1');
  $data = json_decode($_REQUEST['data']);

  $query = "UPDATE role SET type=? WHERE `__pk_id`=?";
  if (!$stmt = $db->prepare($query)) throwError("create_contact 2");
  $stmt->bind_param('ss', $data->type, $data->id);
  $stmt->execute();

  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

function deleteRole() {
  global $db;
  if (!array_key_exists('data', $_REQUEST)) throwError('delete_contact 1');
  $data = json_decode($_REQUEST['data']);

  $query = 'DELETE FROM role WHERE `__pk_id` = ?';
  if (!$stmt = $db->prepare($query)) throwError("delete_role 2");
  $stmt->bind_param('s', $data->role_id);
  $stmt->execute();
  $success = $stmt->affected_rows > 0;
  $stmt->close();
  die(json_encode(['success' => $success]));
}

/*---- UTILITIES ----*/

function readContacts($type, $id) {
  global $db;
  $col = ($type === 'church') ? '_fk_church' : '_fk_person';
  $query = <<<MYSQL
SELECT 'eml', `__pk_id`, email_address, '', '', '', '', '' FROM email WHERE $col = $id
UNION ALL
SELECT 'phn', `__pk_id`, phone_number, '', '', '', '', '' FROM phone WHERE $col = $id
UNION ALL
SELECT 'web', `__pk_id`, url, '', '', '', '', '' FROM website WHERE $col = $id
UNION ALL
SELECT 'add', `__pk_id`, CONCAT(line_1, ', ', line_2, ', ', suburb, ', ', state, ', ', post_code),
line_1, line_2, suburb, state, post_code
 FROM address WHERE $col = $id
MYSQL;
  if (!$stmt = $db->prepare($query)) throwError("read_contacts: $type, $id");
  $stmt->execute();
  $contactId = $contact = $ln1 = $ln2 = $sub = $stt = $pcd = null;
  $stmt->bind_result($type, $contactId, $contact, $ln1, $ln2, $sub, $stt, $pcd);
  $arr = [];
  while ($stmt->fetch()) {
    if ($type === 'add')
      $arr[] = ['type' => $type, 'id' => $contactId, 'contact' => $contact,
        'ln1' => $ln1, 'ln2' => $ln2, 'sub' => $sub, 'stt' => $stt,
        'pcd' => $pcd];
    else
      $arr[] = ['type' => $type, 'id' => $contactId, 'contact' => $contact];
  }
  $stmt->close();
  return $arr;
}

function readRoles($type, $id) {
  global $db;
  if ($type === 'church')
    $query =
      "SELECT r.type, r.__pk_id, p.__pk_id, CONCAT(p.first_name, ' ', p.last_name)"
      . " FROM person p, role r WHERE p.`__pk_id` = r.`_fk_person` AND r.`_fk_church` = $id";
  else
    $query = "SELECT r.type, r.__pk_id, c.__pk_id, c.name"
      . " FROM church c, role r WHERE c.`__pk_id` = r.`_fk_church` AND r.`_fk_person` = $id";

  if (!$stmt = $db->prepare($query)) throwError("read_roles");
  $stmt->execute();
  $role = $rid = $iid = $name = null;
  $stmt->bind_result($role, $rid, $iid, $name);
  $arr = [];
  while ($stmt->fetch())
    $arr[] =
      ['role' => $role, 'role_id' => $rid, 'id' => $iid, 'name' => $name];
  $stmt->close();
  return $arr;
}