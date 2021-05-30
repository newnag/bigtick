<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['interest_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $interest_status = 'Yes';
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';

      $access_token = get_access_token();
      $user_data = get_user_by_token($access_token);   

      if(!$user_data){
        $Result['message'] = 'Exist User';
      }
      else{
        $user_id = $user_data['id'];

        $sql = $mysqli->prepare("DELETE FROM user_interest WHERE user_id = ?");
        $sql->bind_param("i", $user_id);
        $sql->execute();

        $test = 0;
        foreach($_POST['interest_id'] as $interest_id){
          $sql1    =   $mysqli->prepare("INSERT INTO user_interest (user_id , interest_id ,interest_status , createdate , create_ip , status) VALUES (?,?,?,?,?,?) ");
          $sql1->bind_param("iissss", $user_id , $interest_id , $interest_status , $createdate, $create_ip , $status);
          $sql1->execute();
          $test+=1;
        }
  
        $Result['status'] = 'Success';
        $Result['message'] = 'Signup Step 3/4 Success';
        $Result['total_found'] = $test;
        $Result['data'] = get_user($user_id);
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