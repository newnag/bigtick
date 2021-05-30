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
      $myid = $_POST['user_id'];

      $Presql = $mysqli->prepare("SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
      $Presql->bind_param("i", $myid);
      $Presql->execute();
      $Prequery = $Presql->get_result();

      if($Prequery->num_rows > 0){
        while($PreRow = $Prequery->fetch_assoc()){
          $sql = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status = 'Enable' ");
          $sql->bind_param("i", $PreRow['friend_id']);
          $sql->execute();
          $query = $sql->get_result();
          $Row = $query->fetch_assoc();

          $arrData["id"] = $Row["id"];
          $arrData["facebook_id"] = $Row["facebook_id"];
          $arrData["gmail_id"] = $Row["gmail_id"];
          $arrData["apple_id"] = $Row["apple_id"];
          $arrData["access_token"] = $Row["access_token"];
          $arrData["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$User["avatar"];
          $arrData["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$User["cover"];
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
          $arrData["block"] = true;


          $preSqlU = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $preSqlU->bind_param("ii",$myid,$Row["id"]);
          $preSqlU->execute();
          $PrequeryU  =   $preSqlU->get_result();

          if($PrequeryU->num_rows > 0){
            $arrData["friend"] = true;
          }

          // $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
          // $preSqlBlock->bind_param("ii",$myid,$Row["id"]);
          // $preSqlBlock->execute();
          // $PrequeryBlock  =   $preSqlBlock->get_result();
          
          // if($PrequeryBlock->num_rows > 0){
          //   $arrData["block"] = true;
          // }

          $preSql1 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSql1->bind_param("ii",$myid,$Row["id"]);
          $preSql1->execute();
          $Prequery1  =   $preSql1->get_result();

          if($Prequery1->num_rows > 0){
            $arrData["following"] = true;
          }

          ////////////////////////////////////////////////////////////////////////////////////////////

          $Result['total_found'] += 1;
          $Arr_ID[] = $arrData;
        }

        $Result['status']       =   'Success';
        $Result['message']      =   get_responseLang($ac_lang)[0];
        $Result['data'] = $Arr_ID;
      }
      else{
        $Result['message']      =   get_responseLang($ac_lang)[25];
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