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
      $request = 'Requesting';
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $presql = $mysqli->prepare("SELECT * FROM group_member WHERE group_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
      $presql->bind_param("ii", $group_id,$user_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $sql = $mysqli->prepare("UPDATE group_member SET Status = 'Delete' WHERE group_id = ? AND user_id = ? AND Status = 'Enable'");
        $sql->bind_param("ii", $group_id,$user_id);
        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = 'ยกเลิกการเข้าร่วมกลุ่มเรียบร้อย';
        }
        else{
          $Result['message'] = 'ไม่สามารถยกเลิกการเข้าร่วมได้';
        }
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
        $Result['message'] = 'Insert Success';
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