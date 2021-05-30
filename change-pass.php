<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['password']) && !empty($_POST['user_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $user_id = $_POST['user_id'];
      $old_pass = $_POST['old_pass'];
      $confirm_pass = $_POST['confirm_pass'];
      $password = $_POST['password'];
      $pass = password_hash(secure_input($_POST['password']),PASSWORD_DEFAULT);
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];

      if($password == $confirm_pass){
        $presql = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND status <> 'Delete' LIMIT 1");
        $presql->bind_param("i",$user_id);
        $presql->execute();
        $prequery  =   $presql->get_result();
        $Row = $prequery->fetch_assoc();
        
        if(password_verify($old_pass,$Row['password'])){
          if($prequery->num_rows>0){
            $sql = $mysqli->prepare("UPDATE user SET password = ? WHERE id=? ");
            $sql->bind_param("si",$pass,$user_id);
            $sql->execute();
    
            $Result['status'] = 'Success';
            $Result['message'] = get_responseLang($ac_lang)[1];
          }
          else{
            $Result['message'] = get_responseLang($ac_lang)[10];
          }
        }
        else{
          $Result['message'] = "กรอกรหัสผ่านไม่ถูกต้อง";
        } 
      }
      else{
        $Result['message'] = "รหัสผ่านไม่ตรงกัน";
      }
    }
    else{
      $Result['message']      = get_responseLang($ac_lang)[15];
    }    


}else{

    $Result['message']      = get_responseLang($ac_lang)[14];

}


echo json_encode($Result);

?>