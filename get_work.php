<?php
require_once('db.php');
require_once('common.php');


$sql = 'SELECT * FROM Tasks WHERE cracked=0';
stmt = $mysql->stmt_init();
$stmt->prepare($sql);
$data = array();
stmt_bind_assoc($stmt, $data);
$stmt->execute();


if($stmt->fetch()){
  $json = array();
  $json['id'] = $data['id'];
  $json['hs_path'] = $data['hs_path'];
  $json['dict_path'] = $data['dict_path'];
  $json['cracked'] = $data['cracked'];
  echo json_encode($json);
}else {
  echo "No nets";
}


$stmt->close();
$mysql->close();
 ?>
