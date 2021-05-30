<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']  =   0;    
$Result['data']         =   null;


if(!empty($_POST['user_id'])){

    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $myid = trim($_POST['user_id']);

      $Presql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
      $Presql->bind_param("i", $myid);
      $Presql->execute();
      $Prequery = $Presql->get_result();

      if($Prequery->num_rows > 0){
        while($PreRow = $Prequery->fetch_assoc()){
          $sql = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status <> 'Delete' ");
          $sql->bind_param("i", $PreRow['friend_id']);
          $sql->execute();
          $query = $sql->get_result();
          
          $Row = $query->fetch_assoc();
          $arrData["id"] = $Row["id"];
          $arrData["facebook_id"] = $Row["facebook_id"];
          $arrData["gmail_id"] = $Row["gmail_id"];
          $arrData["apple_id"] = $Row["apple_id"];
          $arrData["access_token"] = $Row["access_token"];
          $arrData["avatar"] = $Row["avatar"];
          $arrData["cover"] = $Row["cover"];
          $arrData["username"] = $Row["username"];
          $arrData["first_name"] = $Row["first_name"];
          $arrData["last_name"] = $Row["last_name"];
          $arrData["gender"] = $Row["gender"];
          $arrData["gender_other"] = $Row["gender_other"];
          $arrData["birthdate"] = $Row["birthdate"];
          $arrData["telephone"] = $Row["telephone"];
          $arrData["email"] = $Row["email"];
          $arrData["facebook"] = $Row["facebook"];
          $arrData["line"] = $Row["line"];
          $arrData["address"] = $Row["address"];
          $arrData["country_id"] = $Row["country_id"];
          $arrData["country_other"] = $Row["country_other"];
          $arrData["province_id"] = $Row["province_id"];
          $arrData["province_other"] = $Row["province_other"];
          $arrData["occupation_id"] = $Row["occupation_id"];
          $arrData["work_address"] = $Row["work_address"];
          $arrData["about_me"] = $Row["about_me"];
          $arrData["status"] = $Row["status"];
          $arrData["createdate"] = $Row["createdate"];
          $arrData["create_ip"] = $Row["create_ip"];
          $arrData["updatedate"] = $Row["updatedate"];
          $arrData["update_ip"] = $Row["update_ip"];
          $arrData["lastlogin"] = $Row["lastlogin"];
          $arrData["lastlogin_ip"] = $Row["lastlogin_ip"];

          $sql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
          $sql1->bind_param("i", $Row['id']);
          $sql1->execute();
          $query1  =   $sql1->get_result();
          $Row1    =   $query1->fetch_assoc();

          switch($ac_lang){
            case "en":
              $arrData["member_title"] = $Row1["title_en"];
              break;
            case "th":
              $arrData["member_title"] = $Row1["title_th"];
              break;
    
            default:
              $arrData["member_title"] = $Row1["title_th"];
          }
          
          $arrData["member_coin"] = $Row1["point"];
          $arrData["member_icon"] = SITE_URL."uploads/user_level/images/".$Row1["picture"];

          ////////////////////////////////////////////////////////////////////////////////////////////
          $arrData["friend"] = false;
          $arrData["following"] = false;
          $arrData["block"] = false;


          $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND Status <> 'Delete' ");
          $preSql->bind_param("ii",$myid,$Row["id"]);
          $preSql->execute();
          $Prequery  =   $preSql->get_result();

          if($Prequery->num_rows > 0){
            $arrData["friend"] = true;
          }

          $PreRow    =   $Prequery->fetch_assoc();

          if($PreRow['request_action'] == 'Block'){
            $arrData["block"] = true;
          }

          $preSql1 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSql1->bind_param("ii",$myid,$Row["id"]);
          $preSql1->execute();
          $Prequery1  =   $preSql1->get_result();

          if($Prequery1->num_rows > 0){
            $arrData["following"] = true;
          }

          ////////////////////////////////////////////////////////////////////////////////////////////

          $Result['total_found'] += 1;
          $arrID[] = $arrData;
        }

        $Result['status']       =   'Success';
        $Result['message']      =   get_responseLang($ac_lang)[13];
        $Result['data'] = $arrID;
      }
      else{

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