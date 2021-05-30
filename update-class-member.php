<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';


if(!empty($_POST['user_id']) && !empty($_POST['class_name'])){

    $mysqli     =   db_connect();

    if($mysqli){
      $user_id = $_POST['user_id'];
      $class = $_POST['class_name'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];

      $presql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? LIMIT 1");
      $presql->bind_param("s", $user_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $sql = $mysqli->prepare("UPDATE class_user SET class_name = ?,UpdateDate = ?,UpdateIP = ? WHERE user_id = ?");
        $sql->bind_param("sssi",$class,$createdate,$create_ip,$user_id);
        $sql->execute();
  
        $Result['status']       =   'Success';
        $Result['message']      =   'Update Success';
      }
      else{
        $Result['message'] = 'Exist Username';
      }
    }else{
      $Result['message']      = 'Can not cannect database';
    }    


}else{

    $Result['message']      = 'Missing Variable';

}


echo json_encode($Result);

?>