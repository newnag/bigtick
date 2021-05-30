<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0;
$Result['data']         =   null;

    $mysqli     =   db_connect();

    if($mysqli){

      $sql = $mysqli->prepare("SELECT * FROM type_vote WHERE Status <> 'Delete' ");
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';

        while($Row = $query->fetch_assoc()){

          $FaarRow[] = $Row;

          $Result['total_found'] += 1;
        }

        $Result['data'] = $FaarRow; 
      }
      else{
        $Result['message'] = 'No Found';
      }
    }
    else{
      $Result['message']      = 'Can not cannect database';
    }    




echo json_encode($Result);

?>