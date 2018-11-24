<?php
include_once '../../../../includes/db_connect.php';

function throwError($msg = '') {
  die(json_encode(["a" => "0", "msg" => "error in: " . $msg]));
}

$data = json_decode($_REQUEST['user']);
$q = $data->q;

if ($q == 'read_region') read_region();

function read_region() {
  global $mysqli, $data;
  $username = $data->username;

  // get region info
  $prep_stmt = <<<MYSQL
SELECT r.`__pk_id`, r.name, r.code
FROM region as r,
     login_user as u
WHERE r.`_fk_user_administrator` = u.`__pk_id`
  AND u.username = ?
MYSQL;

  $stmt = $mysqli->prepare($prep_stmt);
  if (!$stmt) throwError('Database error at ' . __LINE__);

  $stmt->bind_param('s', $username);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) die(json_encode(["success" => true,
                                              'region'  => []]));
  $stmt->bind_result($rid, $rname, $rcode);
  $stmt->fetch();
  $region = ['id' => $rid, 'name' => $rname, 'code' => $rcode];
  $stmt->close();

  // get route info
  $prep_stmt = <<<MYSQL
SELECT t.`__pk_id`, t.name, t.driver_mac
FROM route as t
WHERE t.`_fk_region` = ?
MYSQL;

  $stmt = $mysqli->prepare($prep_stmt);
  if (!$stmt) throwError('Database error at ' . __LINE__);

  $stmt->bind_param('s', $region['id']);
  $stmt->execute();
  $stmt->bind_result($tid, $tname, $tmac);
  while ($stmt->fetch())
    $region['routes'][$tid] =
      ['id' => $tid, 'name' => $tname, 'driver_mac' => $tmac];
  $stmt->close();

  $prep_stmt = <<<MYSQL
SELECT c.`__pk_id`,
       c.name,
       c.denomination,
       c.bread_time,
       c.bread_participating,
       c.bread_delivered,
       c.bread_add_line_1,
       c.bread_add_line_2,
       c.bread_add_post_code,
       c.bread_add_suburb,
       c.bread_add_state,
       c.bread_note,
       c.bread_loaves,
       c.bread_bun,
       c.bread_gluten_free
FROM church as c
WHERE c.bread_fk_route = ?
MYSQL;
  $stmt = $mysqli->prepare($prep_stmt);
  if (!$stmt) throwError('Database error at ' . __LINE__);

  foreach ($region['routes'] as $route) {
    $stmt->bind_param('s', $route['id']);
    $stmt->execute();
    $stmt->bind_result($cid, $cname, $cden, $ctime, $cpar, $cdel, $cln1, $cln2,
      $cpc,
      $csub, $cstt, $cnot, $clof, $cbun, $cgf);
    while ($stmt->fetch())
      $region['routes'][$route['id']]['churches'][$cid] =
        ['id'           => $cid, 'name' => $cname,
         'denomination' => $cden, 'time' => $ctime, 'participating' => $cpar,
         'delivered'    => $cdel,
         'line_1'       => $cln1, 'line_2' => $cln2, 'post_code' => $cpc,
         'suburb'       => $csub,
         'state'        => $cstt, 'note' => $cnot, 'loaves' => $clof,
         'buns'         => $cbun,
         'gluten_free'  => $cgf];
  }

  die(json_encode(["success" => true, 'region' => $region]));
}

throwError('end of file');