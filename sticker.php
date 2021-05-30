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
$Result['total_found']  =   0;    
$Result['data']         =   null;

$mysqli     =   db_connect();

$ac_lang = get_headerLang();

if($mysqli){
  $sql    =   $mysqli->prepare("SELECT * FROM sticker WHERE status = 'Enable' ORDER BY sort_order");
  $sql->execute();
  $query  =   $sql->get_result();

  if($query->num_rows>0){
    $arrRow = array();
    $arrData = array();
    while($Row = $query->fetch_assoc()){
      $arrData['id'] = $Row['id'];
      $arrData['picture'] = SITE_URL."uploads/sticker/images/".$Row['picture'];
      $arrData['picture_type'] = $Row['picture_type'];
      $arrData['group_sticker_id'] = $Row['group_sticker_id'];
      ///////////////////////////////////////////////////////////////////////////////////
   
      $arrRow[] = $arrData;
    }

    $Result['status']       =   'Success';
    $Result['message'] = get_responseLang($ac_lang)[0];
    $Result['total_found'] = sizeof($query);
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