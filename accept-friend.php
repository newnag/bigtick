<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $myid = $_POST['user_id'];
      $friend_id = $_POST['friend_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];

      $presql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' LIMIT 1");
      $presql->bind_param("ii",$friend_id,$myid);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $sql = $mysqli->prepare("UPDATE friend SET request_action='Accept',accept_by=?, UpdateDate = ?, UpdateIP = ? WHERE friend_id=? AND user_id=? AND request_action = 'Waiting' AND Status <> 'Delete' ");
        $sql->bind_param("issii",$myid,$createdate,$create_ip,$myid,$friend_id);
        
        if($sql->execute()){
          $action = 'Accept';
          $status = 'Enable';

          $sqlPre1 = $mysqli->prepare("SELECT * FROM friend WHERE friend_id = ? AND user_id = ? AND request_action = 'Waiting' LIMIT 1");
          $sqlPre1->bind_param("ii",$friend_id,$myid);
          $sqlPre1->execute();
          $prequery1  =   $sqlPre1->get_result();
          
          if($prequery1->num_rows > 0){
            $sql1 = $mysqli->prepare("UPDATE friend SET request_action = ?, accept_by = ?, UpdateDate = ?, UpdateIP = ? WHERE friend_id=? AND user_id=? AND request_action = 'Waiting' AND Status <> 'Delete' ");
            $sql1->bind_param("sissii",$action,$myid,$createdate,$create_ip,$friend_id,$myid);
            $sql1->execute();
          }
          else{
            $sql1 = $mysqli->prepare("INSERT INTO friend(user_id,friend_id,request_action,accept_by,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?)");
            $sql1->bind_param("iisisss",$myid,$friend_id,$action,$myid,$createdate,$create_ip,$status);
            $sql1->execute();
          }

          $Result['status'] = 'Success';
          $Result['message'] = get_responseLang($ac_lang)[20];
        }
        else{
          $Result['message'] = get_responseLang($ac_lang)[19];
        }
      }
      else{
        $Result['message'] = get_responseLang($ac_lang)[22];
      }
      
    }
    else{
      $Result['message']      = get_responseLang($ac_lang)[15];
    }    


}else{

    $Result['message']      = get_responseLang($ac_lang)[19];

}


echo json_encode($Result);

?>