<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['post_id']) && !empty($_POST['user_id']) && !empty($_POST['text'])){
  $mysqli = db_connect();

  if($mysqli){
    $myid = $_POST['user_id'];
    $post_id = $_POST['post_id'];
    $text = $_POST['text'];
    $choice = $_POST['choice'];
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

    $presql = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
    $presql->bind_param("i",$post_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $sql = $mysqli->prepare("INSERT INTO post_choice_comment_user(user_id,post_id,text,picture,choice_comment,CreateDate,CreateIP,Status)  VALUES(?,?,?,?,?,?,?,?)");
      $sql->bind_param("iississs",$myid,$post_id,$text,$picture,$choice,$createdate,$create_ip,$status);

      if($sql->execute()){
        $presql2 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? AND Status <> 'Delete' ");
        $presql2->bind_param("i",$post_id);
        $presql2->execute();
        $prequery2 = $presql2->get_result();

        if($prequery2->num_rows > 0){
          $RowPre2 = $prequery2->fetch_assoc();

          if($choice == 0){
            $sum_comment_choice = $RowPre2["choice1_comment"]+1;

            $sql2 = $mysqli->prepare("UPDATE post_choice_meta SET choice1_comment = ?, CreateDate = ?, CreateIP = ? WHERE post_id = ?");
            $sql2->bind_param("issi",$sum_comment_choice,$createdate,$create_ip,$post_id);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
              $Result['data'] = $sql->insert_id;
            }
            else{
              $Result['message'] = 'Cant Insert Data choice meta';
            }
          }
          elseif($choice == 1){
            $sum_comment_choice = $RowPre2["choice2_comment"]+1;

            $sql2 = $mysqli->prepare("UPDATE post_choice_meta SET choice2_comment = ?,CreateDate=?,CreateIP=? WHERE post_id = ?");
            $sql2->bind_param("issi",$sum_comment_choice,$createdate,$create_ip,$post_id);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
              $Result['data'] = $sql->insert_id;
            }
            else{
              $Result['message'] = 'Cant Insert Data choice meta';
            }
          }
          elseif($choice == 2){
            $sum_comment_choice = $RowPre2["choice3_comment"]+1;

            $sql2 = $mysqli->prepare("UPDATE post_choice_meta SET choice3_comment = ?,CreateDate=?,CreateIP=? WHERE post_id = ?");
            $sql2->bind_param("issi",$sum_comment_choice,$createdate,$create_ip,$post_id);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
              $Result['data'] = $sql->insert_id;
            }
            else{
              $Result['message'] = 'Cant Insert Data choice meta';
            }
          }
        }
        else{
          $sum_comment_choice = 1;

          if($choice == 0){
            $sql2 = $mysqli->prepare("INSERT INTO post_choice_meta(post_id,choice1_comment,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
            $sql2->bind_param("iisss",$post_id,$sum_comment_choice,$createdate,$create_ip,$status);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'Insert Success';
              $Result['data'] = $sql->insert_id;
            }
            else{
              $Result['message'] = 'Cant Insert Data choice meta';
            }
          }
          elseif($choice == 1){
            $sql2 = $mysqli->prepare("INSERT INTO post_choice_meta(post_id,choice2_comment,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
            $sql2->bind_param("iisss",$post_id,$sum_comment_choice,$createdate,$create_ip,$status);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'Insert Success';
              $Result['data'] = $sql->insert_id;
            }
            else{
              $Result['message'] = 'Cant Insert Data choice meta';
            }
          }
          elseif($choice == 2){
            $sql2 = $mysqli->prepare("INSERT INTO post_choice_meta(post_id,choice3_comment,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
            $sql2->bind_param("iisss",$post_id,$sum_comment_choice,$createdate,$create_ip,$status);
            if($sql2->execute()){
              $Result['status'] = 'Success';
              $Result['message'] = 'Insert Success';
              $Result['data'] = $sql->insert_id;
            }
            else{
              $Result['message'] = 'Cant Insert Data choice meta';
            }
          }
        }
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