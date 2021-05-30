<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['img_id'])){
  $mysqli     =   db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $img_id = $_POST['img_id'];
    $status = 'Delete';

    $sql = $mysqli->prepare("UPDATE action_post SET Stauts = ? WHERE id = ? ");
    $sql->bind_param("si",$status,$img_id);
    if($sql->execute()){
      $Result['status'] = 'Success';
      $Result['message'] = 'ลบข้อมูลเรียบร้อย';
    }
    else{
      $Result['message'] = "ไม่สามารถลบได้";
    }
    
    ////////////////////////////////////////////////////////////////////////////
  }
  else{
    $Result['message']      = get_responseLang($ac_lang)[15];
  }    
}else{
  $Result['message']      = get_responseLang($ac_lang)[14];
}

echo json_encode($Result);

?>