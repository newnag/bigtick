<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$mysqli     =   db_connect();

$tt = array();

$sql = $mysqli->prepare("SELECT * FROM group_user WHERE id IN (1,2)  AND Status <> 'Delete' ");
$sql->execute();
$query  =   $sql->get_result();
while($Row = $query->fetch_assoc()){
  $bb['com_id'] = $Row['id'];
  $tt[] = $bb;
}


// echo $tt[0]['com_id'];

print_r($tt);

foreach($tt as $gg=> $TT){
  $fnum = array();

  $sql1 = $mysqli->prepare("SELECT * FROM group_member WHERE group_id = ? ");
  $sql1->bind_param('i',$TT['com_id']);
  $sql1->execute();
  $query1 = $sql1->get_result();

  $dd = array();
  while($Row1 = $query1->fetch_assoc()){
    $aa['re_id'] = $Row1['id'];
    $dd[] = $aa;
  }
  
  $rr[$gg] = $dd;

}

print_r($rr);
//$Result['data'] = $tt;

// echo json_encode($Result);

?>