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
    $ac_lang = get_headerLang();

    if($mysqli){

      $sql = $mysqli->prepare("SELECT * FROM type_setting WHERE status <> 'Delete' ");
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';

        while($Row = $query->fetch_assoc()){

          $FaarRow['id'] = $Row['id'];
          switch($ac_lang){
            case "en":
              $FaarRow['name'] = $Row['name_en'];
              break;
            case "th":
              $FaarRow['name'] = $Row['name_th'];
              break;
    
            default:
              $FaarRow['name'] = $Row['name_th'];
          }
          
          $FaarRow['create_date'] = $Row['create_date'];
          $FaarRow['create_ip'] = $Row['create_ip'];
          $FaarRow['status'] = $Row['status'];

          $FData[] = $FaarRow;
          $Result['total_found'] += 1;
        }

        $Result['data'] = $FData; 
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