<?php
header("Content-Type: application/json");

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(E_ALL);

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['choice_comment_id']) && !empty($_POST['user_id'])){
  $mysqli = db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $myid = $_POST['user_id'];
    $choice_comment_id = $_POST['choice_comment_id'];
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];

    $presql = $mysqli->prepare("SELECT * FROM post_choice_comment_user WHERE id = ? ");
    $presql->bind_param("i",$choice_comment_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $status = 'Enable';

      $presql2 = $mysqli->prepare("SELECT * FROM post_choice_comment_like WHERE choice_comment_id = ? AND user_id = ? AND Status <> 'Delete' ");
      $presql2->bind_param("ii",$choice_comment_id,$myid);
      $presql2->execute();
      $prequery2 = $presql2->get_result();

      if($prequery2->num_rows == 0){
        $sql = $mysqli->prepare("INSERT INTO post_choice_comment_like(choice_comment_id,user_id,CreateDate,CreateIP,Status)  VALUES(?,?,?,?,?)");
        $sql->bind_param("iisss",$choice_comment_id,$myid,$createdate,$create_ip,$status);

        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = get_responseLang($ac_lang)[6];
        }
        else{
          $Result['message'] = get_responseLang($ac_lang)[19];
        }
      }
      else{
        $status = 'Delete';

        $sql = $mysqli->prepare("UPDATE post_choice_comment_like SET Status=? WHERE choice_comment_id=? AND user_id=? ");
        $sql->bind_param("sii",$status,$choice_comment_id,$myid,);

        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = get_responseLang($ac_lang)[7];
        }
        else{
          $Result['message'] = get_responseLang($ac_lang)[19];
        }
      }
    }
    else{
      $Result['message'] = get_responseLang($ac_lang)[12];
    }
  }
  else{
    $Result['message'] = get_responseLang($ac_lang)[15];
  }

}
else{
  $Result['message'] = get_responseLang($ac_lang)[14];
}

echo json_encode($Result);

?>