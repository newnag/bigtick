<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['group_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $group_id = $_POST['group_id'];
      $status = 'Delete';

      $presql = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? LIMIT 1");
      $presql->bind_param("i", $group_id);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows == 0){
        $Result['message'] = 'Dont have Group';
      }
      else{
        $sql = $mysqli->prepare("UPDATE group_user SET Status=? WHERE id=?");
        $sql->bind_param("si", $status,$group_id);
        $sql->execute();

        $sql2 = $mysqli->prepare("UPDATE group_interest SET Status=? WHERE group_id=?");
        $sql2->bind_param("si", $status,$group_id);
        $sql2->execute();
  
        $Result['status'] = 'Success';
        $Result['message'] = 'Delete Success';
        $Result['data'] = $group_id;
      }
    }
    else{
      $Result['message']      = 'Can not cannect database';
    }    


}else{

    $Result['message']      = 'Missing Variable';

}


echo json_encode($Result);

?>