<?php



header("Content-Type: application/json");



// ini_set('display_errors',1);

// ini_set('display_startup_errors',1);

// error_reporting(E_ALL);



$AllowPublic = true; 

require('../administrator/include/config.inc.php');

require('../administrator/include/function.inc.php');



$Result['status']       =   'Error';

$Result['message']      =   '';

$Result['total_found']   =   0;    

$Result['data']         =   null;



$mysqli     =   db_connect();



$regisError = false;



if($mysqli){

  if(!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['gender']) && !empty($_POST['birthdate']) && !empty($_POST['telephone']) && !empty($_POST['email']) && !empty($_POST['username']) && !empty($_POST['password'])){



    $username       =       $_POST['email'];

    $first_name     =       $_POST['first_name'];

    $last_name      =       $_POST['last_name'];

    $gender         =       $_POST['gender'];

    $gender_other   =       $_POST['gender_other'];

    $birthdate      =       $_POST['birthdate'];

    $telephone      =       $_POST['telephone'];

    $email          =       $_POST['email'];

    $facebook       =       $_POST['facebook'];

    $line           =       $_POST['line'];

    $createdate     =       date("Y-m-d H:i:s");

    $create_ip      =       $_SERVER["REMOTE_ADDR"];

    $access_token   =       generate_token();

    $address        =       $_POST['address'];

    $country_id     =       $_POST['country_id'];

    $country_other  =       $_POST['country_other'];

    $province_id    =       $_POST['province_id'];

    $province_other =       $_POST['province_other'];

    $occupation_id  =       $_POST['occupation_id'];

    $occupation_other  =       $_POST['occupation_other'];

    $work_address   =       $_POST['work_address'];

    $about_me       =       $_POST['about_me'];

    $interest_status    =   "Yes";

    $status = "Enable";

    $CountInterest  =   0;

    $interest = $_POST['interest_id'];



    $interArr = explode(",",$interest);



    foreach($interArr as $interest_id){

      $sqlInter    =   $mysqli->prepare("INSERT INTO user_interest(user_id , interest_id , interest_status , createdate , create_ip) VALUES (?,?,?,?,?) ");

      $sqlInter->bind_param("iisss", $user_id , $interest_id , $interest_status, $createdate , $create_ip);

      if($sqlInter->execute()){

        $CountInterest  +=  1;

      }

      else{

        $error += 1;

      }

    }



    $username   =   $_POST['username'];

    $password   =   password_hash($_POST['password'],PASSWORD_DEFAULT);



    $presql1 = $mysqli->prepare("SELECT * FROM user WHERE telephone = ? OR email = ? AND status <> 'Delete' ");

    $presql1->bind_param("ss", $telephone,$email);

    $presql1->execute();

    $Prequery1 = $presql1->get_result();



    if($Prequery1->num_rows > 0){

      $regisError = true;

    }



    $presql2 = $mysqli->prepare("SELECT * FROM user WHERE username = ? AND status <> 'Delete' ");

    $presql2->bind_param("s", $username);

    $presql2->execute();

    $Prequery2 = $presql2->get_result();



    if($Prequery2->num_rows > 0){

      $regisUserError = true;

    }

    $avatar = "avatar.png";
    $cover = "default.jpg";


    if(!$regisError){

      if(!$regisUserError){

        $sql    =   $mysqli->prepare("INSERT INTO user(avatar,cover,username,password,first_name,last_name,gender,gender_other,birthdate,telephone,email,facebook,line,address,country_id,country_other,province_id,province_other,occupation_id,occupation_other,work_address,about_me,status,createdate,create_ip) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $sql->bind_param("ssssssssssssssisisissssss",$avatar,$cover,$username, $password, $first_name, $last_name,$gender,$gender_other,$birthdate,$telephone,$email,$facebook,$line,$address,$country_id,$country_other,$province_id,$province_other,$occupation_id,$occupation_other,$work_address,$about_me,$status,$createdate,$create_ip);

        

        if($sql->execute()){

          $user_id = $sql->insert_id;

  

          $class = 'Member';

          $point = 0;

          $createdate = date("Y-m-d H:i:s");

          $create_ip = $_SERVER["REMOTE_ADDR"];

          $status = 'Enable';

    

          $sql2 = $mysqli->prepare("INSERT INTO class_user (user_id,class_name,point,UpdateDate,UpdateIP,Status) VALUES(?,?,?,?,?,?)");

          $sql2->bind_param("isisss", $user_id,$class,$point,$createdate,$create_ip,$status);

          $sql2->execute();

    

          $ac_lang = get_headerLang();

    

          $sql3 = $mysqli->prepare("INSERT INTO user_setting (user_id,lang,create_date,create_ip,status) VALUES(?,?,?,?,?)");

          $sql3->bind_param("issss", $user_id,$ac_lang,$createdate,$create_ip,$status);

          $sql3->execute();

    

          $Result['status']       = 'Success';

          $Result['message']      = 'สมัครสมาชิกสำเร็จ';

          $Result['data']         = get_user($user_id); 

        }

        else{

          $Result['message'] = 'สมัครสมาชิกไม่สำเร็จ';

        }

      }

      else{

        $Result['message']  = 'มี username ในระบบแล้ว';

      }

    }

    else{

      $Result['message']  = 'มีเบอร์โทรหรืออีเมล์นี้ในระบบแล้ว';

    }

    



  }

  else{

    $Result['message']      = 'Missing Variable';

  }       



}

else{

  $Result['message']      = 'Can not cannect database';

}



echo json_encode($Result);



?>

