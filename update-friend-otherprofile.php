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
      $profile_id = $_POST['profile_id'];
      $action = $_POST['action'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      for($i=0;$i<sizeof($profile_id);$i++){
        foreach($action as $ac){
          if($ac == 'block'){
            $sql = $mysqli->prepare("UPDATE friend SET request_action='Block' WHERE friend_id=? AND user_id=? ");
            $sql->bind_param("ii",$profile_id,$myid);
  
            if($sql->execute()){
              $sql1 = $mysqli->prepare("UPDATE friend SET request_action='Block' WHERE friend_id=? AND user_id=? ");
              $sql1->bind_param("ii",$myid,$profile_id);
              $sql1->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Data Success';
            }
            else{
              $Result['message'] = 'No Match Friend';
            }
          }
          if($ac == 'follow'){
            $sql = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $sql->bind_param("ii",$myid,$profile_id);
            $sql->execute();
            $query  =   $sql->get_result();
  
            if($query->num_rows > 0){
              $Result['message'] = 'You Followed';
            }
            else{
              $sqlA = $mysqli->prepare("INSERT INTO friend_follow(user_id,follow_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?) ");
              $sqlA->bind_param("iisss",$myid,$profile_id,$createdate,$create_ip,$status);
  
              if($sqlA->execute()){
                $Result['status'] = 'Success';
                $Result['message'] = 'Insert Data Success';
              }
              else{
                $Result['message'] = 'No Match Friend';
              }
            }
          }
          if($ac == 'add'){
            $Presql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? ");
            $Presql->bind_param("ii",$myid,$profile_id);
            $Presql->execute();
            $Prequery  =   $Presql->get_result();
            
            $action = 'Waiting';
  
            if($Prequery->num_rows == 0){
              $sql = $mysqli->prepare("INSERT INTO friend(user_id,friend_id,request_action,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
              $sql->bind_param("iissss",$myid,$profile_id,$action,$createdate,$create_ip,$status);
  
              if($sql->execute()){  
                $Result['status'] = 'Success';
                $Result['message'] = 'Add Friend Success';
              }
              else{
                $Result['message']  = 'Can not add friend';
              }
            }
            else{
              $Row = $prequery->fetch_assoc();
              if($Row["Status"] == 'Delete'){
                $sql = $mysqli->prepare("UPDATE friend SET CreateDate=?,CreateIP=?,request_action=?,Status='Enable' WHERE friend_id=? AND user_id=? ");
                $sql->bind_param("sssii",$createdate,$create_ip,$action,$profile_id,$myid);
                $sql->execute();
  
                $Result['status'] = 'Success';
                $Result['message'] = 'Add Friend Success';
              }
              else{
                $Result['message'] = 'You have sent Request or have he in friend-list.';
              }
            }
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