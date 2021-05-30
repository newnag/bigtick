<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

////////////////////////////////////////////////////////////

function genRandomPass($length = 6){
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

////////////////////////////////////////////////////////////

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['email'])){
  $mysqli     =   db_connect();

  if($mysqli){
    $email = $_POST['email'];

    $sql = $mysqli->prepare("SELECT * FROM user WHERE email = ? AND Status <> 'Delete' ");
    $sql->bind_param("s",$email);
    $sql->execute();
    $query  =  $sql->get_result();

    if($query->num_rows > 0){
      $Row = $query->fetch_assoc();
      $random_Pass = genRandomPass(8);

      if(!empty($random_Pass)){

        #-------------------------------------------------------------------
        # PROCESS Email Send
        #-------------------------------------------------------------------
        $fm = "bigtickofficial@gmail.com"; // *** ต้องใช้อีเมล์ @yourdomain.com เท่านั้น  ***
        $to = $email; // อีเมล์ที่ใช้รับข้อมูลจากแบบฟอร์ม

        $mail = new PHPMailer();
        $mail->CharSet = "utf-8"; 

        /* ------------------------------------------------------------------------------------------------------------- */
        /* ตั้งค่าการส่งอีเมล์ โดยใช้ SMTP ของ โฮสต์ */
        $mail->IsSMTP();
        $mail->Mailer = "smtp";
        $mail->SMTPAuth = true;
        //$mail->SMTPSecure = 'ssl'; // บรรทัดนี้ ให้ Uncomment ไว้ เพราะ Mail Server ของโฮสต์ ไม่รองรับ SSL.
        $mail->Host = "smtp.mailgun.org"; //ใส่ SMTP Mail Server ของท่าน
        $mail->Port = "587"; // หมายเลข Port สำหรับส่งอีเมล์
        $mail->Username = "noreply@mailer.gramickhouse.com"; //ใส่ Email Username ของท่าน (ที่ Add ไว้แล้วใน Plesk Control Panel)
        $mail->Password = "25a101cf0cfa9970f0f1d2b3fb0ea9d2-d32d817f-46154e9b"; //ใส่ Password ของอีเมล์ (รหัสผ่านของอีเมล์ที่ท่านตั้งไว้) 
        /* ------------------------------------------------------------------------------------------------------------- */

        $mail->SetFrom("$fm ", "Bigtick");
        $mail->AddAddress($to);
        $mail->Subject = "รหัสชั่วคราวของคุณ";
        $mail->Body = "รหัสชั่วคราวของคุณคือ ".$random_Pass;
        $mail->WordWrap = 50;  
        //
        if(!$mail->Send()) {
          $Result['message']  = $mail->ErrorInfo;
        }
        else{
          $passRen = password_hash($random_Pass,PASSWORD_DEFAULT);

          $sql2 = $mysqli->prepare("UPDATE user SET password = ? WHERE email = ? AND status <> 'Delete' ");
          $sql2->bind_param("ss",$passRen,$email);
          if($sql2->execute()){
            $Result['status'] = 'Success';
            $Result['message'] = 'ส่งอีเมล์สำเร็จ กรุณารอไม่เกิน 5 นาที';
          }
          else{
            $Result['message'] = 'เปลี่ยนรหัสผ่านไม่สำเร็จ';
          }
        }
      }
    }
    else{
      $Result['message'] = 'Not Found User';
    }
  }
  else{
    $Result['message']      = 'Can not cannect database';
  }   

}else{
  $Result['message']  = 'Missing Variable';
}

echo json_encode($Result);

?>