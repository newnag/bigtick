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
      $user_id = $_POST['user_id'];
      $group_id = $_POST['group_id'];
      $request = '';
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';
      $mode_request = $_POST['mode'];
      $approve_by = '';

      $presql = $mysqli->prepare("SELECT * FROM group_member WHERE user_id = ? AND group_id = ? LIMIT 1");
      $presql->bind_param("ii", $user_id,$group_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        if($mode_request === 'Approve'){
          $presql1 = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? LIMIT 1");
          $presql1->bind_param("i", $group_id);
          $presql1->execute();
          $prequery1  =   $presql1->get_result();
          $PreRow = $prequery1->fetch_assoc();

          $request = 'Approve';
          $approve_by = $PreRow['owner'];
        }
        elseif($mode_request === 'Accept'){
          $request = 'Accept';
          $approve_by = '';
        }

        $sql = $mysqli->prepare("UPDATE group_member SET request_status=?, approve_by=?, UpdateDate=?, UpdateIP=? WHERE group_id=? AND user_id=?");
        $sql->bind_param("sissii",$request,$approve_by,$createdate,$create_ip,$group_id,$user_id);
        $sql->execute();
  
        $Result['status'] = 'Success';
        $Result['message'] = 'Update Success';

        if($mode_request === 'Approve'){
          ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          // เพิ่มการแจ้งเตือน

          $type_noti = "accept_group";

          $sql_noti = $mysqli->prepare("INSERT INTO noti(type,my_id,group_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
          $sql_noti->bind_param("siisss",$type_noti,$user_id,$group_id,$createdate,$create_ip,$status);
          $sql_noti->execute();
        }
      }
      else{
        $Result['message'] = 'No Match Member Group';
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