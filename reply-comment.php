<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['comment_id']) && !empty($_POST['user_id'])){
  $mysqli = db_connect();

  if($mysqli){
    $myid = $_POST['user_id'];
    $comment_id = $_POST['comment_id'];
    $text = $_POST['text'];
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];
    $status = 'Enable';
    $picture = $_POST['picture'];

    // if($_FILES['picture']["name"]){
    //   $target_dir     =   "../uploads/comment/images";
    //   $avatar_name    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["picture"]["name"]);
    //   $target_file    =   $target_dir .'/'.$avatar_name;
    //   $uploadOk       =   1;
    //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    //   $maxsize = 200000000;

    //   $check = getimagesize($_FILES["picture"]["tmp_name"]);
    //   $sizeFile = filesize($_FILES["picture"]["tmp_name"]);

    //   // if($check !== false) {
    //   //   $uploadOk = 1;
    //   // } else {
    //   //   $uploadOk = 0;
    //   // }

    //   if($sizeFile > $maxsize){
    //     $uploadOk = 0; 
    //   }

    //   if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "mp4" ) {
    //     $uploadOk = 0;
    //   }

    //   if ($uploadOk == 1) {
    //     if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
    //         $picture = $avatar_name;
    //     }
    //   }
    // }

    $presql = $mysqli->prepare("SELECT * FROM post_comment WHERE id = ? AND Status <> 'Delete' ");
    $presql->bind_param("i",$comment_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $sql = $mysqli->prepare("INSERT INTO post_comment_relative(comment_id,user_id,text,pic,CreateDate,CreateIP,Status)  VALUES(?,?,?,?,?,?,?)");
      $sql->bind_param("iisssss",$comment_id,$myid,$text,$picture,$createdate,$create_ip,$status);

      if($sql->execute()){
        $Result['status'] = 'Success';
        $Result['message'] = 'Insert Success';
        $Result['data'] = $sql->insert_id;
      }
      else{
        $Result['message'] = 'Cant Insert Data check post_id or user_id or comment';
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