<?php

header("Content-Type: application/json");

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(E_ALL);

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']   =   0;    
$Result['data']         =   null;

$mysqli     =   db_connect();

if($mysqli){
  $telephone      =       secure_input($_POST['telephone']);
  $email          =       secure_input($_POST['email']);

  $sql = $mysqli->prepare("SELECT * FROM user WHERE telephone = ? OR email = ? AND status <> 'Delete' ");
  $sql->bind_param("ss", $telephone,$email);
  $sql->execute();
  $query = $sql->get_result();

  if($query->num_rows > 0){
    $Result['message']  = 'เบอร์โทรศัพท์หรืออีเมล์มีในระบบ';
  }
  else{
    $Result['status']       = 'Success';
    $Result['message']      = 'ใช้เบอร์โทรศัพท์และอีเมล์นี้ได้';
  }
}
else{
  $Result['message']      = 'Can not cannect database';
}

?>