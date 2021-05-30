<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['friend_id']) && !empty($_POST['user_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $myid = $_POST['user_id'];
      $friend_id = $_POST['friend_id'];
      $action = 'Waiting';
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $presql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action <> 'Waiting' AND Status <> 'Delete' LIMIT 1");
      $presql->bind_param("ii",$myid,$friend_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $Row = $prequery->fetch_assoc();
        $sql = $mysqli->prepare("UPDATE friend SET Status='Delete' WHERE friend_id=? AND user_id=? ");
        $sql->bind_param("ii",$friend_id,$myid);
        $sql->execute();

        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[21];
      }
      else{
        $sql = $mysqli->prepare("INSERT INTO friend(user_id,friend_id,request_action,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
        $sql->bind_param("iissss",$myid,$friend_id,$action,$createdate,$create_ip,$status);
        $sql->execute();

        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[21];
        $Result['data'] = $sql->insert_id;
      }
    }
    else{
      $Result['message']      = get_responseLang($ac_lang)[15];
    }    


}else{

    $Result['message']      = get_responseLang($ac_lang)[14];

}


echo json_encode($Result);

?>