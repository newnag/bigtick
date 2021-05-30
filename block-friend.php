<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];
      $friend_id = $_POST['friend_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];

      $Presql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' LIMIT 1 ");
      $Presql->bind_param("ii",$myid,$friend_id);
      $Presql->execute();
      $Prequery = $Presql->get_result();

      if($Prequery->num_rows == 0){
        $Presql2 = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' LIMIT 1 ");
        $Presql2->bind_param("ii",$myid,$friend_id);
        $Presql2->execute();
        $Prequery2 = $Presql2->get_result();

        if($Prequery2->num_rows == 0){
          $action = 'Block';
          $status = 'Enable';

          $sql = $mysqli->prepare("INSERT INTO friend(user_id,friend_id,request_action,block_by,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?)");
          $sql->bind_param("iisisss",$myid,$friend_id,$action,$myid,$createdate,$create_ip,$status);
          if($sql->execute()){
            $Result['status'] = 'Success';
            $Result['message'] = 'Block Success';

            $arrData['user_id'] = $myid;
            $arrData['friend_id'] = $friend_id;
            $arrData['block'] = true;

            $Result['data'] = $arrData;
          }
        }
        else{
          $sql = $mysqli->prepare("UPDATE friend SET request_action='Block',block_by=?,UpdateDate=?,UpdateIP=? WHERE friend_id=? AND user_id=? ");
          $sql->bind_param("issii",$myid,$createdate,$create_ip,$friend_id,$myid);
    
          if($sql->execute()){
            $sql1 = $mysqli->prepare("UPDATE friend SET request_action='Block',block_by=?,UpdateDate=?,UpdateIP=? WHERE friend_id=? AND user_id=? ");
            $sql1->bind_param("issii",$myid,$createdate,$create_ip,$myid,$friend_id);
            $sql1->execute();
    
            $Result['status'] = 'Success';
            $Result['message'] = 'Block Success';

            $arrData['user_id'] = $myid;
            $arrData['friend_id'] = $friend_id;
            $arrData['block'] = true;

            $Result['data'] = $arrData;
          }
          else{
            $Result['message'] = 'No Match Friend';
          }
        }
      }
      else{
        $Presql2 = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' LIMIT 1 ");
        $Presql2->bind_param("ii",$friend_id,$myid);
        $Presql2->execute();
        $Prequery2 = $Presql2->get_result();

        if($Prequery2->num_rows == 0){
          $sql = $mysqli->prepare("UPDATE friend SET Status = 'Delete' WHERE friend_id=? AND user_id=? AND request_action = 'Block' AND Status = 'Enable' ");
          $sql->bind_param("ii",$friend_id,$myid);
    
          if($sql->execute()){
            $Result['status'] = 'Success';
            $Result['message'] = 'UnBlock Success';
  
            $arrData['user_id'] = $myid;
            $arrData['friend_id'] = $friend_id;
            $arrData['block'] = false;
  
            $Result['data'] = $arrData;
          }
          else{
            $Result['message'] = 'No Match Friend';
          }
        }
        else{
          $sql = $mysqli->prepare("UPDATE friend SET request_action = 'Accept', UpdateDate = ?, UpdateIP = ?, block_by = 0 WHERE friend_id=? AND user_id=? AND request_action = 'Block' AND Status = 'Enable' ");
          $sql->bind_param("ssii",$createdate,$create_ip,$friend_id,$myid);
          if($sql->execute()){
            $sql2 = $mysqli->prepare("UPDATE friend SET request_action = 'Accept', UpdateDate = ?, UpdateIP = ?, block_by = 0 WHERE friend_id=? AND user_id=? AND request_action = 'Block' AND Status = 'Enable' ");
            $sql2->bind_param("ssii",$createdate,$create_ip,$myid,$friend_id);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'UnBlock Success';
    
              $arrData['user_id'] = $myid;
              $arrData['friend_id'] = $friend_id;
              $arrData['block'] = false;
    
              $Result['data'] = $arrData;
            }
            else{
              $Result['message']      = 'Can not Unblock';
            }
          }
          else{
            $Result['message']      = 'Can not Unblock';
          }
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