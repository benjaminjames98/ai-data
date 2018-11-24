<?php
header("Content-Type: application/json; charset=UTF-8");

require "../../../../includes/db_connect.php";

function throwError($msg = null) {
  echo json_encode(["a" => "0", "msg" => "error in: " . $msg]);
  exit();
}

$db = getMysqli();
if ($db == -1) throwError();

/*---- Prep ----*/

$q = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
  $q = $_POST['q'];
elseif ($_SERVER['REQUEST_METHOD'] == 'GET')
  $q = $_GET['q'];
else throwError('Request Method');

/*---- $q ----*/

if ($q == 'read_regions') readRegions();
else throwError('End Script');

function readRegions() {
  global $db;
  $query = "SELECT `__pk_id`, name FROM region ORDER BY `__pk_id`";
  if (!$result = $db->prepare($query)) throwError('read_regions');
  $result->execute();
  $result->bind_result($id, $name);
  $regions = [];
  while ($result->fetch())
    $regions[] = ["id" => $id, "name" => $name, "churches" => []];
  $result->close();

  for ($i = 0; $i < count($regions); $i++)
    $regions[$i]['churches'] = readChurches($regions[$i]['id']);

  echo json_encode(['regions' => $regions]);
  exit();
}

function readChurches($region) {
  global $db;

  $query = <<<MYSQL
SELECT
  c.name,
  IFNULL(c.bread_time, '00:00:59'),
  c.bread_delivered
FROM church as c, route as ro
WHERE bread_participating = 'yes' AND c.bread_fk_route = ro.`__pk_id` AND ro.`_fk_region` = ?
ORDER BY bread_time
MYSQL;

  if (!$result1 = $db->prepare($query)) throwError('read_churches');
  $result1->bind_param('i', $region);
  $result1->execute();
  $result1->bind_result($nam, $tim, $del);
  $churches = [];
  while ($result1->fetch())
    $churches[] = ["name" => $nam, "time" => $tim, "delivered" => $del];
  $result1->close();
  return $churches;

}