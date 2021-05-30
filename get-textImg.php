<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;

$mysqli     =   db_connect();
$ac_lang = get_headerLang();

if($mysqli){
  $post_id = $_POST['post_id'];
  $choice = $_POST['choice'];

  $sql = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND choice = ? AND status <> 'Delete' ");
  $sql->bind_param("ii",$post_id,$choice);
  $sql->execute();
  $query  =   $sql->get_result();

  if($query->num_rows>0){
    while($Row = $query->fetch_assoc()){
      $arrData[] = $Row;
    }

    $arrFData[] = $arrData;

    $Result['status'] = 'Success';
    $Result['message'] = 'Get Data Success';

    $Result['data'] = $arrFData;
  }
  else{
    $Result['message'] = 'No Match Choice';
  }
}
else{
  $Result['message']      = 'Can not cannect database';
} 


echo json_encode($Result);

?>