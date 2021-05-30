<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0;
$Result['data']         =   null;

if(!empty($_POST['user_id'])){
  $mysqli     =   db_connect();

  if($mysqli){
    $myid = $_POST['user_id'];

    $sql = $mysqli->prepare("SELECT * FROM user_setting WHERE user_id = ? AND Status <> 'Delete' ");
    $sql->bind_param('i',$myid);
    $sql->execute();
    $query  =   $sql->get_result();

    if($query->num_rows>0){
      $Result['status'] = 'Success';
      $Result['message'] = 'Get Data Success';

      while($Row = $query->fetch_assoc()){

        // $FaarRow[] = $Row;

        $Result['total_found'] += 1;
        $Result['data'] = $Row; 
      }

      
    }
    else{
      $Result['message'] = 'No Found';
    }
  }
  else{
    $Result['message']      = 'Can not cannect database';
  }  
}
else{
  $Result['message']      = 'Please fill user id';
}
      




echo json_encode($Result);

?>