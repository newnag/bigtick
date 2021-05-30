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
$Result['total_found']  =   0;    
$Result['data']         =   null;

$mysqli     =   db_connect();

$ac_lang = get_headerLang();

if($mysqli){
  $user_id = $_POST['user_id'];
  $sql    =   $mysqli->prepare("SELECT * FROM user WHERE status = 'Enable' ");
  $sql->execute();
  $query  =   $sql->get_result();

  if($query->num_rows>0){
    $arrRow = array();
    $arrData = array();
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
      $sql1->bind_param("i", $Row["id"]);
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

      $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
      $sql_publisher_following->bind_param("i",$Row["id"]);
      $sql_publisher_following->execute();
      $query_publisher_following = $sql_publisher_following->get_result();
      $Row_publisher_following = $query_publisher_following->fetch_assoc();

      $arrData['following_user'] = $Row_publisher_following['id'];

      //////////////////////////////////////////////////////////////////////////////////

      $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
      $sql_publisher_follower->bind_param("i",$Row["id"]);
      $sql_publisher_follower->execute();
      $query_publisher_follower = $sql_publisher_follower->get_result();
      $Row_publisher_follower = $query_publisher_follower->fetch_assoc();

      $arrData['follower_user'] = $Row_publisher_follower['id'];

      ///////////////////////////////////////////////////////////////////////////////////

      $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
      $sql_publisher_friend->bind_param("i",$Row["id"]);
      $sql_publisher_friend->execute();
      $query_publisher_friend = $sql_publisher_friend->get_result();
      $Row_publisher_friend = $query_publisher_friend->fetch_assoc();

      $arrData['friend_user'] = $Row_publisher_friend['id'];

      ///////////////////////////////////////////////////////////////////////////////////

      $sql_publisher_friend_mutual = $mysqli->prepare("SELECT COUNT(id) FROM friend WHERE user_id IN (SELECT friend_id FROM `friend` WHERE user_id IN (SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id <> ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
      $sql_publisher_friend_mutual->bind_param("iii",$Row["id"],$Row["id"],$Row["id"]);
      $sql_publisher_friend_mutual->execute();
      $query_publisher_friend_mutual = $sql_publisher_friend_mutual->get_result();
      $Row_publisher_friend_mutual = $query_publisher_friend_mutual->fetch_assoc();

      $arrData['friend_mutual_user'] = $Row_publisher_friend_mutual['COUNT(id)'];

      ///////////////////////////////////////////////////////////////////////////////////
   
      $arrRow[] = $arrData;
    }

    $Result['status']       =   'Success';
    $Result['message'] = get_responseLang($ac_lang)[0];
    $Result['total_found'] = sizeof($query);
    $Result['data'] = $arrRow;
  }
  else{
    $Result['message']      = 'Exit Member!';
  }
  

}else{

        $Result['message']      = 'Can not cannect database';
}    


echo json_encode($Result);

?>