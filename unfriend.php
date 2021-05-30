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
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $presql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND Status <> 'Delete' LIMIT 1");
      $presql->bind_param("ii",$myid,$friend_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows > 0){
        $Row = $prequery->fetch_assoc();
        
        $sql = $mysqli->prepare("UPDATE friend SET Status='Delete' WHERE friend_id=? AND user_id=? ");
        $sql->bind_param("ii",$friend_id,$myid);
        $sql->execute();

        $sql1 = $mysqli->prepare("UPDATE friend SET Status='Delete' WHERE friend_id=? AND user_id=? ");
        $sql1->bind_param("ii",$myid,$friend_id);
        $sql1->execute();

        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[24];
      }
      else{
        $Result['message'] = get_responseLang($ac_lang)[23];
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