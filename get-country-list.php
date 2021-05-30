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

if($mysqli){

        $country_json   =       file_get_contents('https://s.boxme.asia/api/v1/locations/countries/');
        $country_array  =       json_decode($country_json,true);

        // foreach($country_array['data'] as $country){
        //         $tmp['id']      =       $country['id'];
        //         $tmp['code']    =       $country['country_code'];
        //         $tmp['thai']    =       $country['country_name'];
        //         $tmp['english'] =       $country['country_name'];
        //         $tmp['status']  =       'Enable';
        //         $Data[]         =       $tmp;
        // }

        $tmp['id']      =       1;
        $tmp['code']    =       66;
        $tmp['thai']    =       "ประเทศไทย";
        $tmp['english'] =       "Thailand";
        $tmp['status']  =       'Enable';
        $Data[]         =       $tmp;


        $Other['id']    =   99;
        $Other['code']  =   0;
        $Other['thai']  =   "อื่นๆ";
        $Other['english'] =     "Other";
        $Other['status']  =     "Enable";

        $Data[]     =   $Other;
        
        $Result['status']       = 'Success';
        $Result['message']      = 'Get Data Success';
        $Result['total_found']  = count($country_array['data'])+1;
        $Result['data']         = $Data;



}else{

        $Result['message']      = 'Can not cannect database';
}    



echo json_encode($Result);

?>