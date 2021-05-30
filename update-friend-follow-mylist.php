<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];
      $friend_id = $_POST['friend_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = $_POST['status'];

      $sql = $mysqli->prepare("UPDATE friend_follow SET Status=?,UpdateDate=?,UpdateIP=? WHERE follow_id=? AND user_id=? ");
      $sql->bind_param("sssii",$status,$createdate,$create_ip,$myid,$friend_id);

      if($sql->execute()){
        $Result['status'] = 'Success';
        $Result['message'] = 'Update Data Success';
      }
      else{
        $Result['message'] = 'No Match Friend';
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