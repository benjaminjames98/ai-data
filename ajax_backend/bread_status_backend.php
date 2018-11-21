<?php
header("Content-Type: application/json; charset=UTF-8");

require "../includes/db_connect.php";

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
else if ($_SERVER['REQUEST_METHOD'] == 'GET')
  $q = $_GET['q'];
else throwError('Request Method');

/*---- $q ----*/

if ($q == 'read_regions') readRegions();
else if ($q == 'read_routes') readRoutes();
else throwError('End Script');

function readRegions() {
  global $db;
  $query = "SELECT `__pk_id`, name FROM region";
  if (!$result = $db->prepare($query)) throwError('read_regions');
  $result->execute();
  $result->bind_result($id, $name);
  $regions = [];
  while ($result->fetch())
    $regions[] = ["id" => $id, "name" => $name];
  $result->close();
  $db->close();
  echo json_encode(['regions' => $regions]);
  exit();
}

function readRoutes() {
  global $db;
  $region = $_GET['region'];

  $query = "SELECT __pk_id, name FROM route WHERE  `_fk_region` = ? ORDER BY `__pk_id`";
  if (!$result = $db->prepare($query)) throwError('read_routes');
  $result->bind_param('i', $region);
  $result->execute();
  $result->bind_result($id, $name);
  $routes = [];
  while ($result->fetch())
    $routes[] = ['id' => $id, "name" => $name, "churches" => []];
  $result->close();

  $size = count($routes);
  for ($i = 0; $i < $size; $i++)
    $routes[$i]['churches'] = readChurches($routes[$i]['id']);

  $query = "SELECT name FROM region WHERE  `__pk_id` = ?";
  if (!$result = $db->prepare($query)) throwError('read_routes_2');
  $result->bind_param('i', $region);
  $result->execute();
  $result->bind_result($name);
  $result->fetch();
  $result->close();

  $db->close();
  echo json_encode(['name' => $name, 'routes' => $routes]);
  exit();
}

function readChurches($route) {
  global $db;

  $query = <<<MYSQL
SELECT
  name,
  IFNULL(bread_time, '23:00:00'),
  bread_delivered
FROM church
WHERE bread_participating = 'yes' AND bread_fk_route = ?
ORDER BY bread_time
MYSQL;

  if (!$result1 = $db->prepare($query)) throwError('read_churches');
  $result1->bind_param('i', $route);
  $result1->execute();
  $result1->bind_result($nam, $tim, $del);
  $churches = [];
  while ($result1->fetch())
    $churches[] = ["name" => $nam, "time" => $tim, "delivered" => $del];
  $result1->close();
  return $churches;

}