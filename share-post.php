<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');
// require('../api/function/function_api.php');


///////////////////////////////////////////////////////////////////
$mysqli     =   db_connect();

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['user_id']) && !empty($_POST['post_id'])){
    if($mysqli){
      $user_id = $_POST['user_id'];
      $post_id = $_POST['post_id'];
      $type_share = $_POST['type_share'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $Func_sql = $mysqli->prepare("SELECT * FROM post_share WHERE user_id = ? AND post_id = ? AND status <> 'Delete' LIMIT 1");
      $Func_sql->bind_param("ii",$user_id,$post_id);
      $Func_sql->execute();
      $Func_query = $Func_sql->get_result();

      if($Func_query->num_rows == 0){
        $sql = $mysqli->prepare("INSERT INTO post_share(user_id,post_id,type_share,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?) ");
        $sql->bind_param("iissss",$user_id,$post_id,$type_share,$createdate,$create_ip,$status);
        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = 'แชร์สำเร็จ';
        }
        else{
          $Result['message'] = 'แชร์ไม่สำเร็จ';
        }
      }
      else{
        $Result['message'] = 'คุณได้แชร์โพสนี้ไปแล้ว';
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