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

if(!empty($_POST['post_id'])){
    if($mysqli){
      $user_id = $_POST['user_id'];
      $post_id = $_POST['post_id'];
      $group_id = $_POST['group_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';
      $mode = $_POST['mode'];

      $pass = 0;

      if($mode == "group"){
        foreach($group_id as $arrGroup_id){
          $Func_sql = $mysqli->prepare("SELECT * FROM post_share WHERE user_id = ? AND post_id = ? AND group_id = ? AND status <> 'Delete' ");
          $Func_sql->bind_param("iii",$user_id[0],$post_id,$arrGroup_id);
          $Func_sql->execute();
          $Func_query = $Func_sql->get_result();

          if($Func_query->num_rows == 0){
            $sql = $mysqli->prepare("INSERT INTO post_share(user_id,post_id,group_id,type_share,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?) ");
            $sql->bind_param("iiissss",$user_id[0],$post_id,$arrGroup_id,$mode,$createdate,$create_ip,$status);
            if($sql->execute()){
              $pass += 1;
            }
          }
        }

        if($pass > 0){
          $Result['status'] = 'Success';
          $Result['message'] = 'แชร์สำเร็จ';
        }
        else{
          $Result['message'] = 'แชร์ไม่สำเร็จ';
        }
      }
      elseif($mode == "friend"){
        foreach($user_id as $arrUser_id){
          $Func_sql = $mysqli->prepare("SELECT * FROM post_share WHERE user_id = ? AND post_id = ? AND status <> 'Delete' ");
          $Func_sql->bind_param("ii",$arrUser_id,$post_id);
          $Func_sql->execute();
          $Func_query = $Func_sql->get_result();

          if($Func_query->num_rows == 0){
            $sql = $mysqli->prepare("INSERT INTO post_share(user_id,post_id,type_share,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?) ");
            $sql->bind_param("iissss",$arrUser_id,$post_id,$mode,$createdate,$create_ip,$status);
            if($sql->execute()){
              $pass += 1;
            }
          }
        }

        if($pass > 0){
          $Result['status'] = 'Success';
          $Result['message'] = 'แชร์สำเร็จ';
        }
        else{
          $Result['message'] = 'แชร์ไม่สำเร็จ';
        }
      }
      else{
        $Result['message'] = 'กรุณาเลือกโหมด';
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