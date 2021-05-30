<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['user_id'])){

    $mysqli     =   db_connect();

    if($mysqli){
      $user_id = $_POST['user_id'];
      $class = 'Member';
      $point = 0;
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $presql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? LIMIT 1");
      $presql->bind_param("s", $user_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $Result['message'] = 'Exist Username';
      }
      else{
        $sql = $mysqli->prepare("INSERT INTO class_user (user_id,class_name,point,UpdateDate,UpdateIP,Status) VALUES(?,?,?,?,?,?)");
        $sql->bind_param("ssisss", $user_id,$class,$point,$createdate,$create_ip,$status);
        $sql->execute();
  
        $Result['status']       =   'Success';
        $Result['message']      =   'Insert Success';
        $Result['data'] = $sql->insert_id;
      }
    }
    else{
      $Result['message']      = 'Can not cannect database';
    }    


}else{

    $Result['message']      = 'Missing Variable';

}


echo json_encode($Result);

?>