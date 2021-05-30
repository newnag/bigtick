<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['post_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $post_id = $_POST['post_id'];
      $text = $_POST['text'];
      $status = 'Delete';

      $presql = $mysqli->prepare("SELECT id FROM post WHERE id = ? LIMIT 1");
      $presql->bind_param("i", $post_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows == 0){
        $Result['message'] = 'Dont have Post';
      }
      else{
        $sql = $mysqli->prepare("UPDATE post SET Status=? WHERE id=?");
        $sql->bind_param("si", $status,$post_id);
        if($sql->execute()){
          $createdate = date('Y-m-d H:i:s');
          $createIP = $_SERVER["REMOTE_ADDR"];
          $delete_status = 'Enable';

          $sql1 = $mysqli->prepare("INSERT INTO reason_post_delete(post_id,text,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?) ");
          $sql1->bind_param("issss", $post_id,$text,$createdate,$createIP,$delete_status);
          $sql1->execute();

          $Result['status'] = 'Success';
          $Result['message'] = 'Delete Success';
          $Result['data'] = $post_id;
        }
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