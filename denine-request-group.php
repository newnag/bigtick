<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';


if(!empty($_POST['group_id']) && !empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $group_id = $_POST['group_id'];
      $user_id = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT * FROM group_member WHERE group_id = ? AND user_id = ? AND request_status = 'Requesting' AND Status <> 'Delete' ");
      $sql->bind_param("ii", $group_id,$user_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows > 0){
        $sql1 = $mysqli->prepare("UPDATE group_member SET Status = 'Delete' WHERE group_id = ? AND user_id = ? AND request_status = 'Requesting' ");
        $sql1->bind_param("ii",$group_id,$user_id);
        if($sql1->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = 'ปฏิเสธคำขอเข้ากลุ่มแล้ว';
        }
        else{
          $Result['message'] = 'การปฏิเสธล้มเหลว';
        }
      }
      else{
        $Result['message'] = 'Not Found Data';
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