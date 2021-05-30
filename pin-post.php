<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['post_id']) && !empty($_POST['user_id'])){
  $mysqli = db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $user_id = $_POST['user_id'];
    $post_id = $_POST['post_id'];
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];
    $status = "Enable";

    $presql = $mysqli->prepare("SELECT * FROM post_pin WHERE post_id = ? AND User_id = ? AND Status <> 'Delete' ");
    $presql->bind_param("ii",$post_id,$user_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows == 0){
      $sql = $mysqli->prepare("INSERT INTO post_pin(post_id,user_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?) ");
      $sql->bind_param("iisss",$post_id,$user_id,$createdate,$create_ip,$status);
      if($sql->execute()){
        $Result['status'] = 'Success';
        $Result['message'] = "ปักหมุดสำเร็จ";
      }
      else{
        $Result['message'] = "ไม่สามารถปักหมุดได้";
      }
    }
    else{
      $sql = $mysqli->prepare("UPDATE post_pin SET Status = 'Delete' WHERE post_id = ? AND user_id = ? ");
      $sql->bind_param("ii",$post_id,$user_id);
      if($sql->execute()){
        $Result['status'] = 'Success';
        $Result['message'] = "ยกเลิกปักหมุดแล้ว";
      }
      else{
        $Result['message'] = "ไม่สามารถยกเลิกการปักหมุดได้";
      }
    }
  }
  else{
    $Result['message'] = get_responseLang($ac_lang)[15];
  }

}
else{
  $Result['message'] = 'Missing Variable';
}

echo json_encode($Result);

?>