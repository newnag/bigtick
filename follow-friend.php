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

if(!empty($_POST['user_id']) && !empty($_POST['friend_id'])){
    if($mysqli){
      $myid = $_POST['user_id'];
      $friend_id = $_POST['friend_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';
      $setting_follow = false;

      $Func_sql = $mysqli->prepare("SELECT * FROM user_setting WHERE user_id = ? AND status <> 'Delete' ");
      $Func_sql->bind_param("i",$friend_id);
      $Func_sql->execute();
      $Func_query = $Func_sql->get_result();
      $Func_Row = $Func_query->fetch_assoc();

      if($Func_Row['who_follow'] == 1){
        $setting_follow = true;
      }
      elseif($Func_Row['who_follow'] == 2){
        $sqlPreFriend = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
        $sqlPreFriend->bind_param("i", $friend_id);
        $sqlPreFriend->execute();
        $queryPreFrien  =   $sqlPreFriend->get_result();
        while($RowPreFriend = $queryPreFrien->fetch_assoc()){
          $arrFriend[] = $RowPreFriend['friend_id'];
        }

        if(in_array($myid,$arrFriend)){
          $setting_follow = true;
        }
      }
      elseif($Func_Row['who_follow'] == 3){
        $sqlPreFriend = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
        $sqlPreFriend->bind_param("i", $friend_id);
        $sqlPreFriend->execute();
        $queryPreFrien  =   $sqlPreFriend->get_result();
        while($RowPreFriend = $queryPreFrien->fetch_assoc()){
          $sqlPreFriendMu = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $sqlPreFriendMu->bind_param("i", $RowPreFriend["friend_id"]);
          $sqlPreFriendMu->execute();
          $queryPreFrienMu  =   $sqlPreFriendMu->get_result();
          while($RowPreFriendMu = $queryPreFrienMu->fetch_assoc()){
            $arrFriend[] = $RowPreFriendMu['friend_id'];
          }
        }

        if(in_array($myid,$arrFriend)){
          $setting_follow = true;
        }
      }
      elseif($Func_Row['who_follow'] == 4){
        $sqlPreFollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
        $sqlPreFollow->bind_param("i", $friend_id);
        $sqlPreFollow->execute();
        $queryPreFollow =   $sqlPreFollow->get_result();
        while($RowPreFollow = $queryPreFollow->fetch_assoc()){
          $arrFollow[] = $RowPreFollow['follow_id'];
        }

        if(in_array($myid,$arrFollow)){
          $setting_follow = true;
        }
      }
      else{
        $setting_follow = false;
      }

      ////////////////////////////////////////////////////////////////////////////////

      if($setting_follow){
        $Presql = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
        $Presql->bind_param("ii",$myid,$friend_id);
        $Presql->execute();
        $Prequery  =   $Presql->get_result();
  
        if($Prequery->num_rows == 0){
          $sql = $mysqli->prepare("INSERT INTO friend_follow(user_id,follow_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?) ");
          $sql->bind_param("iisss",$myid,$friend_id,$createdate,$create_ip,$status);
  
          if($sql->execute()){
            $Result['status'] = 'Success';
            $Result['message'] = 'Follow Success';

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // เพิ่มการแจ้งเตือน

            $type_noti = "follow";

            $sql_noti = $mysqli->prepare("INSERT INTO noti(type,user_id,my_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
            $sql_noti->bind_param("siisss",$type_noti,$myid,$friend_id,$createdate,$create_ip,$status);
            $sql_noti->execute();
          }
          else{
            $Result['message'] = 'Cant Follow';
          }
        }
        else{
          $status = 'Delete';
  
          $sql = $mysqli->prepare("UPDATE friend_follow SET Status = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? AND follow_id = ?");
          $sql->bind_param("sssii",$status,$createdate,$create_ip,$myid,$friend_id);
  
          if($sql->execute()){
            $Result['status'] = 'Success';
            $Result['message'] = 'UnFollow Success';
          }
          else{
            $Result['message'] = 'Cant UnFollow';
          }
        }
      }
      else{
        $Result['message']  = 'Can not Follow';
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