<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['reply_comment_id']) && !empty($_POST['user_id'])){
  $mysqli = db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $myid = $_POST['user_id'];
    $reply_comment_id = $_POST['reply_comment_id'];
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];
    $showData = false;

    $presql = $mysqli->prepare("SELECT * FROM post_comment_relative WHERE id = ? ");
    $presql->bind_param("i",$reply_comment_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $status = 'Enable';

      $presql2 = $mysqli->prepare("SELECT * FROM post_comment_relative_like WHERE reply_comment_id = ? AND user_like = ? AND Status <> 'Delete' ");
      $presql2->bind_param("ii",$reply_comment_id,$myid);
      $presql2->execute();
      $prequery2 = $presql2->get_result();

      if($prequery2->num_rows == 0){
        $sql = $mysqli->prepare("INSERT INTO post_comment_relative_like(reply_comment_id,user_like,CreateDate,CreateIP,Status)  VALUES(?,?,?,?,?)");
        $sql->bind_param("iisss",$reply_comment_id,$myid,$createdate,$create_ip,$status);

        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = get_responseLang($ac_lang)[6];
          $showData = true;
        }
        else{
          $Result['message'] = get_responseLang($ac_lang)[19];
        }
      }
      else{
        $status = 'Delete';

        $sql = $mysqli->prepare("UPDATE post_comment_relative_like SET Status=? WHERE reply_comment_id=? AND user_like=?");
        $sql->bind_param("sii",$status,$reply_comment_id,$myid);

        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = get_responseLang($ac_lang)[7];
          $showData = true;
        }
        else{
          $Result['message'] = get_responseLang($ac_lang)[19];
        }
      }

      // if($showData){
      //   $sqlF1 = $mysqli->prepare("SELECT COUNT(user_like) as reply_comment_like FROM post_comment_relative_like WHERE reply_comment_id = ? AND user_like <> 0 AND Status <> 'Delete' ");
      //   $sqlF1->bind_param("i",$reply_comment_id);
      //   $sqlF1->execute();
      //   $queryF1  =   $sqlF1->get_result();

      //   if($queryF1->num_rows>0){
      //     $Row = $queryF1->fetch_assoc();
      //   }
      //   else{
      //     $Result['data'] = null;
      //   }

      //   $Result['data'] = $Row;
      // }
      // else{
      //   $Result['data'] = null;
      // }
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
  $Result['message'] = 'Missing Variable';
}

echo json_encode($Result);

?>