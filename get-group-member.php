<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0; 
$Result['data']         =   null;


if(!empty($_POST['group_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $group_id = $_POST['group_id'];

      $sql = $mysqli->prepare("SELECT user.* FROM group_member JOIN user ON group_member.user_id = user.id WHERE group_id = ? AND request_status IN ('Approve','Accept') AND group_member.Status <> 'Delete'");
      $sql->bind_param("i", $group_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){

        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';
        
        while($Row = $query->fetch_assoc()){
          $arrData["id"] = $Row["id"];
          $arrData["facebook_id"] = $Row["facebook_id"];
          $arrData["gmail_id"] = $Row["gmail_id"];
          $arrData["apple_id"] = $Row["apple_id"];
          $arrData["access_token"] = $Row["access_token"];
          $arrData["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$Row["avatar"];
          $arrData["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$Row["cover"];
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

          $arrRow[] = $arrData;
          $Result['total_found'] += 1;
        }
        $Result['data'] = $arrRow;
       
      }
      else{
        $Result['message'] = 'Dont have Member';
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