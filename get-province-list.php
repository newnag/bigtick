<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 0");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
header("Content-Type: application/json;charset=UTF-8");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']  =   0;    
$Result['data']         =   null;



$mysqli     =   db_connect();

if($mysqli){



        $sql    =   $mysqli->prepare("SELECT * FROM province WHERE status = 'Enable'");
        $sql->execute();
        $query  =   $sql->get_result();
        while($Row = $query->fetch_assoc()){
                
                $Data[]     =   $Row;

        }

        $Other['id']            =   0;
        $Other['thai']          =   "อื่นๆ";
        $Other['english']       =   "Other";
        $Other['status']        =   "Enable";

        $Data[]     =   $Other;
        
        $Result['status']       = 'Success';
        $Result['message']      = 'Get Data Success';
        $Result['total_found']  = $query->num_rows+1;
        $Result['data']         = $Data;



}else{

        $Result['message']      = 'Can not cannect database';
}    



echo json_encode($Result);

?>