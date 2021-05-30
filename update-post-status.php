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

////////////////////////////////////////////////////////////////////
$mysqli     =   db_connect();
$ac_lang = get_headerLang();

if($mysqli){
  $post_id = $_POST['post_id'];
  $status = $_POST['status'];

  if(!empty($post_id) && !empty($status)){
    $sql =  $mysqli->prepare("UPDATE post SET post_vote_status = ? WHERE id = ?");
    $sql->bind_param("si",$status,$post_id);
    if($sql->execute()){
      $Result['status'] = 'Success';
      $Result['message'] = "แก้ไขสถานะการโหวตสำเร็จ";
    }
    else{
      $Result['message'] = "แก้ไขสถานะการโหวตไม่สำเร็จ";
    }
  }
  else{
    $Result['message'] = "ใส่ post id หรือ สถานะให้ครบ";
  }
}
else{
  $Result['message'] = get_responseLang($ac_lang)[15];
}

echo json_encode($Result);

?>