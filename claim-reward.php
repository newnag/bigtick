<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;

if(!empty($_POST['user_id']) && !empty($_POST['reward_id'])){
  $mysqli     =   db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $user_id = $_POST['user_id'];
    $reward_id = $_POST['reward_id'];

    $presql = $mysqli->prepare("SELECT * FROM reward WHERE id = ? AND status <> 'Delete' ");
    $presql->bind_param("i",$reward_id);
    $presql->execute();
    $prequery = $presql->get_result();
    $prerow = $prequery->fetch_assoc();

    $preuser = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND status <> 'Delete' ");
    $preuser->bind_param("i",$user_id);
    $preuser->execute();
    $userquery = $preuser->get_result();
    $userrow = $userquery->fetch_assoc();

    if($userrow['point'] >= $prerow['amount']){
      $sql = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND status <> 'Delete' ");
      $sql->bind_param("i",$userrow['user_id']);
      $sql->execute();
      $query = $sql->get_result();
      $Row = $query->fetch_assoc();

      $arrData["name"] = $Row['first_name']." ".$Row['last_name'];
      $arrData["coin_used"] = $prerow['amount'];

      $Result['status'] = 'Success';
      $Result['message'] = 'Claim Reward Success';
      $Result['data']  =  $arrData;
    }
    else{
      $Result['message'] = get_responseLang($ac_lang)[26];
    }
  }
  else{
    $Result['message']      = get_responseLang($ac_lang)[15];
  }    
}else{
  $Result['message']      = get_responseLang($ac_lang)[14];
}

echo json_encode($Result);

?>