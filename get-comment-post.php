<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']      =   '';

if(!empty($_POST['post_id'])){
  $mysqli = db_connect();

  if($mysqli){
    $post_id = $_POST['post_id'];
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];
    $status = 'Enable';

    $presql = $mysqli->prepare("SELECT * FROM post_comment WHERE post_id = ? AND user_comment > 0 AND Status <> 'Delete' ");
    $presql->bind_param("i",$post_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $arrData = array();
      $arrPost = array();
      $arrIndex = array();

      while($RowComment = $prequery->fetch_assoc()){
        $arrPost['id'] = $RowComment['post_id'];
        $arrData['text'] = $RowComment['comment'];
        $arrData['picture'] = $RowComment['comment_pic'];

        $sqluser = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status <> 'Delete' ");
        $sqluser->bind_param("i",$RowComment['user_comment']);
        $sqluser->execute();
        $Userquery = $sqluser->get_result();

        if($Userquery->num_rows > 0){
          $RowUser = $Userquery->fetch_assoc();

          $arrData['user_comment'] = $RowUser;

          $arrPost['comment'] = $arrData;

          $arrIndex[] = $arrPost;

          $Result['status'] = 'Success';
          $Result['message'] = 'Get Data Success';
          $Result['data'] = $arrIndex;
        }
        else{
          $arrData['user_comment'] = null;
        }
      }
    }
    else{
      $Result['message'] = 'Not Found Post';
    }
  }
  else{
    $Result['message'] = 'Can not cannect database';
  }

}
else{
  $Result['message'] = 'Missing Variable';
}

echo json_encode($Result);

?>