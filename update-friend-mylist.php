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
      $friend_id = $_POST['friend_id']; // send from array
      $action = $_POST['action'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      for($i=0;$i<sizeof($friend_id);$i++){
        foreach($action as $ac){
          if($ac == 'block'){
            $sql = $mysqli->prepare("UPDATE friend SET request_action='Block' WHERE friend_id=? AND user_id=? ");
            $sql->bind_param("ii",$friend_id[$i],$myid);
  
            if($sql->execute()){
              $sql1 = $mysqli->prepare("UPDATE friend SET request_action='Block' WHERE friend_id=? AND user_id=? ");
              $sql1->bind_param("ii",$myid,$friend_id[$i]);
              $sql1->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Data Success';
            }
            else{
              $Result['message'] = 'No Match Friend';
            }
          }
          if($ac == 'follow'){
            $sql = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? ");
            $sql->bind_param("ii",$myid,$friend_id[$i]);
            $sql->execute();
            $query  =   $sql->get_result();
  
            if($query->num_rows > 0){
              $Result['message'] = 'You Followed';
            }
            else{
              $sqlA = $mysqli->prepare("INSERT INTO friend_follow(user_id,follow_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?) ");
              $sqlA->bind_param("iisss",$myid,$friend_id[$i],$createdate,$create_ip,$status);
  
              if($sqlA->execute()){
                $Result['status'] = 'Success';
                $Result['message'] = 'Insert Data Success';
              }
              else{
                $Result['message'] = 'No Match Friend';
              }
            }
          }
          if($ac == 'delete'){
            $sql = $mysqli->prepare("UPDATE friend SET Status='Delete' WHERE friend_id=? AND user_id=? ");
            $sql->bind_param("ii",$friend_id[$i],$myid);
            
            if($sql->execute()){
              $sql1 = $mysqli->prepare("UPDATE friend SET Status='Delete' WHERE friend_id=? AND user_id=? ");
              $sql1->bind_param("ii",$myid,$friend_id[$i]);
              $sql1->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Data Success';
            }
            else{
              $Result['message'] = 'No Match Friend';
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