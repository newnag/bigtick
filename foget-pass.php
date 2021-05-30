<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['email'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $email = $_POST['email'];

      $presql = $mysqli->prepare("SELECT * FROM user WHERE email = ? AND Status <> 'Delete' LIMIT 1");
      $presql->bind_param("i", $email);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows == 0){
        $Result['message'] = 'ไม่พบสมาชิก';
      }
      else{
        function randomPassword() {
          $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
          $pass = array(); //remember to declare $pass as an array
          $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
          for ($i = 0; $i < 8; $i++) {
              $n = rand(0, $alphaLength);
              $pass[] = $alphabet[$n];
          }
          return implode($pass); //turn the array into a string
        }

        $gen_pass = randomPassword();

        echo $gen_pass;
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