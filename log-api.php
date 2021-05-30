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
  $sql    =   $mysqli->prepare("SELECT * FROM log_api");
  $sql->execute();
  $query  =   $sql->get_result();

  if($query->num_rows>0){
    $arrRow = array();
    $arrData = array();
    while($Row = $query->fetch_assoc()){
      $arrRow[] = $Row;
    }

    $Result['status']       =   'Success';
    $Result['message'] = get_responseLang($ac_lang)[0];
    $Result['data'] = $arrRow;
  }
  else{
    $Result['message']      = 'Exit Member!';
  }
  

}else{

        $Result['message']      = 'Can not cannect database';
}    


echo json_encode($Result);

?>