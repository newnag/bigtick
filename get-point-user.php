<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $user_id = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT user.first_name,class_user.class_name,class_user.point FROM class_user 
                              JOIN user ON class_user.user_id = user.id 
                              WHERE user_id = ? LIMIT 1");
      $sql->bind_param("i", $user_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Row = $query->fetch_assoc();

        $Result['status'] = 'Success';
        $Result['message'] = 'Insert Success';
        $Result['data'] = $Row;
      }
      else{
        $Result['message'] = 'Dont have Member';
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