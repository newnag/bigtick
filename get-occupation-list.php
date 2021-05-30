<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 0");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
header("Content-Type: application/json");

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



        $sql    =   $mysqli->prepare("SELECT * FROM occupation_list WHERE status = 'Enable'");
        $sql->execute();
        $query  =   $sql->get_result();
        while($Row = $query->fetch_assoc()){
                
                $arrayData["id"]   =   $Row["id"];
                switch($ac_lang){
                        case "en":
                                $arrayData["name"] = $Row["name_en"];
                                break;

                        case "th":
                                $arrayData["name"] = $Row["name_th"];
                                break;

                        default:
                                $arrayData["name"] = $Row["name_th"];
                }

                $Data[] = $arrayData;

        }


        
        $Result['status']       = 'Success';
        $Result['message']      = 'Get Data Success';
        $Result['total_found']  = $query->num_rows+1;
        $Result['data']         = $Data;



}else{

        $Result['message']      = 'Can not cannect database';
}    



echo json_encode($Result);

?>