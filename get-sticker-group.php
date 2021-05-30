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
$Result['data']         =   null;

$mysqli     =   db_connect();
$ac_lang = get_headerLang();

if($mysqli){
  $sql    =   $mysqli->prepare("SELECT * FROM sticker_group ");
  $sql->bind_param("i",$group_id);
  $sql->execute();
  $query  =   $sql->get_result();

  if($query->num_rows>0){
    $arrRow = array();
    while($Row = $query->fetch_assoc()){
      $group_sticker['id'] = $Row['id'];
      $group_sticker['group_name'] = $Row['group_name'];
      $group_sticker['group_picture'] = SITE_URL."uploads/sticker/images/".$Row['group_picture'];
   
      $arrRow[] = $group_sticker;
    }

    $Result['status']       =   'Success';
    $Result['message'] = get_responseLang($ac_lang)[0];
    $Result['data'] = $arrRow;
  }
  else{
    $Result['message']      = 'ไม่มีข้อมูล!';
  }
}else{
  $Result['message']      = 'Can not cannect database';
}    

echo json_encode($Result);

?>