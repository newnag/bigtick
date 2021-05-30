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
      $user_id = $_POST['user_id'];
      $group_id = $_POST['group_id'];
      $request = 'Inviting';
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $presql = $mysqli->prepare("SELECT * FROM group_member WHERE group_id = ? AND user_id = ? AND request_status = 'Inviting' AND Status <> 'Delete' LIMIT 1");
      $presql->bind_param("ii", $group_id,$user_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $sql = $mysqli->prepare("UPDATE group_member SET Status = 'Delete' WHERE group_id = ? AND user_id = ? AND Status = 'Inviting'");
        $sql->bind_param("ii", $group_id,$user_id);
        $sql->execute();

        $Result['status'] = 'Success';
        $Result['message'] = 'ยกเลิกคำเชิญสำเร็จ';
      }
      else{
        $presql1 = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? LIMIT 1");
        $presql1->bind_param("i", $group_id);
        $presql1->execute();
        $prequery1  =   $presql1->get_result();

        $PreRow = $prequery1->fetch_assoc();

        if($PreRow["public_status"] === "Public"){
          $request = 'Approve';
        }

        $sql = $mysqli->prepare("INSERT INTO group_member(group_id,user_id,request_status,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
        $sql->bind_param("iissss", $group_id,$user_id,$request,$createdate,$create_ip,$status);
        $sql->execute();

        $Result['status'] = 'Success';
        $Result['message'] = 'เชิญสำเร็จ';
        $Result['data'] = $sql->insert_id;

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // เพิ่มการแจ้งเตือน

        $type_noti = "invite_group";

        $sql_noti = $mysqli->prepare("INSERT INTO noti(type,group_id,my_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
        $sql_noti->bind_param("siisss",$type_noti,$group_id,$user_id,$createdate,$create_ip,$status);
        $sql_noti->execute();
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