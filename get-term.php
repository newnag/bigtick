<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;

$mysqli     =   db_connect();
$ac_lang = get_headerLang();

if($mysqli){
  $sql = $mysqli->prepare("SELECT * FROM term WHERE status <> 'Delete' ");
  $sql->execute();
  $query  =   $sql->get_result();

  if($query->num_rows>0){
    $Row = $query->fetch_assoc();

    $Result['status'] = 'Success';
    $Result['message'] = 'Get Data Success';

    $Result['data'] = $Row;
  }
  else{
    $Result['message'] = 'No Match Post';
  }
}
else{
  $Result['message']      = 'Can not cannect database';
} 


echo json_encode($Result);

?>