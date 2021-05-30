<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['group_id']) && !empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $group_id = $_POST['group_id'];
      $user_id = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT * FROM group_member WHERE group_id = ? AND user_id = ? AND request_status IN ('Approve','Accept') AND Status <> 'Delete' ");
      $sql->bind_param("ii", $group_id,$user_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows > 0){
        $sql_owner = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? AND owner = ? AND Status <> 'Delete' ");
        $sql_owner->bind_param("ii", $group_id,$user_id);
        $sql_owner->execute();
        $query_owner  =   $sql_owner->get_result();

        if($query_owner->num_rows > 0){
          $Result['message'] = 'ไม่สามารถออกจากกลุ่มได้ เนื่องจากคุณเป็นผู้ดูแล';
        }
        else{
          $sql1 = $mysqli->prepare("UPDATE group_member SET Status = 'Delete' WHERE group_id = ? AND user_id = ? AND Status = 'Enable' ");
          $sql1->bind_param("ii", $group_id,$user_id);
          if($sql1->execute()){
            $Result['status'] = 'Success';
            $Result['message'] = 'ออกจากกลุ่มเรียบร้อยแล้ว';
          }
          else{
            $Result['message'] = 'ไม่สามารถออกจากกลุ่มได้';
          }
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