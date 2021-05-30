<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['action']) && !empty($_POST['user_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $user_id = $_POST['user_id'];
      $action = trim($_POST['action']);
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];

      $presql = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND status <> 'Delete' LIMIT 1");
      $presql->bind_param("i",$user_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        if($action == "Delete"){
          $sql = $mysqli->prepare("UPDATE user SET status = 'Delete' WHERE id=? ");
          $sql->bind_param("i",$user_id);
          $sql->execute();

          $Result['status'] = 'Success';
          $Result['message'] = get_responseLang($ac_lang)[1];
        }
        elseif($action == "Close"){
          $Row = $prequery->fetch_assoc();
          if($Row['status'] == 'Close'){
            $sql = $mysqli->prepare("UPDATE user SET status = 'Enable' WHERE id=? ");
            $sql->bind_param("i",$user_id);
            $sql->execute();
  
            $Result['status'] = 'Success';
            $Result['message'] = get_responseLang($ac_lang)[1];
          }
          else{
            $sql = $mysqli->prepare("UPDATE user SET status = 'Close' WHERE id=? ");
            $sql->bind_param("i",$user_id);
            $sql->execute();
  
            $Result['status'] = 'Success';
            $Result['message'] = get_responseLang($ac_lang)[1];
          }
        }
        else{
          $Result['message'] = "ระบุ action";
        }
      }
      else{
        $Result['message'] = get_responseLang($ac_lang)[10];
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