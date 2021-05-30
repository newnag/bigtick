<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0;
$Result['data']         =   null;


if(!empty($_POST['user_id']) && !empty($_POST['profile_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $myid = $_POST['user_id'];
      $profile_id = $_POST['profile_id'];

      $sql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id <> ? AND Status <> 'Delete' ");
      $sql->bind_param("ii", $profile_id,$myid);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';

        while($Row = $query->fetch_assoc()){
          //$ArrData_user = array();

          $user_data = get_user($Row["friend_id"]);

          $arrData["id"] = $user_data["id"];
          $arrData["facebook_id"] = $user_data["facebook_id"];
          $arrData["gmail_id"] = $user_data["gmail_id"];
          $arrData["apple_id"] = $user_data["apple_id"];
          $arrData["access_token"] = $user_data["access_token"];
          $arrData["avatar"] = $user_data["avatar"];
          $arrData["cover"] = $user_data["cover"];
          $arrData["username"] = $user_data["username"];
          $arrData["first_name"] = $user_data["first_name"];
          $arrData["last_name"] = $user_data["last_name"];
          $arrData["gender"] = $user_data["gender"];
          $arrData["gender_other"] = $user_data["gender_other"];
          $arrData["birthdate"] = $user_data["birthdate"];
          $arrData["telephone"] = $user_data["telephone"];
          $arrData["email"] = $user_data["email"];
          $arrData["facebook"] = $user_data["facebook"];
          $arrData["line"] = $user_data["line"];
          $arrData["address"] = $user_data["address"];
          $arrData["country_id"] = $user_data["country_id"];
          $arrData["country_other"] = $user_data["country_other"];
          $arrData["province_id"] = $user_data["province_id"];
          $arrData["province_other"] = $user_data["province_other"];
          $arrData["occupation_id"] = $user_data["occupation_id"];
          $arrData["work_address"] = $user_data["work_address"];
          $arrData["about_me"] = $user_data["about_me"];
          $arrData["status"] = $user_data["status"];
          $arrData["createdate"] = $user_data["createdate"];
          $arrData["create_ip"] = $user_data["create_ip"];
          $arrData["updatedate"] = $user_data["updatedate"];
          $arrData["update_ip"] = $user_data["update_ip"];
          $arrData["lastlogin"] = $user_data["lastlogin"];
          $arrData["lastlogin_ip"] = $user_data["lastlogin_ip"];

          ////////////////////////////////////////////////////////////////////////////////////////////

          $sql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
          $sql1->bind_param("i", $Row["friend_id"]);
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
          $arrData["follow"] = false;
          $arrData["block"] = false;


          $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $preSql->bind_param("ii",$myid,$Row["friend_id"]);
          $preSql->execute();
          $Prequery  =   $preSql->get_result();

          if($Prequery->num_rows > 0){
            $arrData["friend"] = true;
          }

          $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND Status <> 'Delete' ");
          $preSqlBlock->bind_param("ii",$myid,$Row["friend_id"]);
          $preSqlBlock->execute();
          $PrequeryBlock  =   $preSqlBlock->get_result();
          $PreRow    =   $PrequeryBlock->fetch_assoc();

          if($PreRow['request_action'] == 'Block'){
            $arrData["block"] = true;
          }

          $preSql1 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSql1->bind_param("ii",$myid,$Row["friend_id"]);
          $preSql1->execute();
          $Prequery1  =   $preSql1->get_result();

          if($Prequery1->num_rows > 0){
            $arrData["follow"] = true;
          }

          ////////////////////////////////////////////////////////////////////////////////////////////

          $arrData["follower"] = false;
          $arrData["friend_request"] = false;

          $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSqlfollow->bind_param("ii",$user_data["id"],$myid);
          $preSqlfollow->execute();
          $Prequeryfollow  =   $preSqlfollow->get_result();

          if($Prequeryfollow->num_rows > 0){
            $arrData["follower"] = true;
          }

          $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
          $preSqlrequest->bind_param("ii",$myid,$user_data["id"]);
          $preSqlrequest->execute();
          $Prequeryrequest  =   $preSqlrequest->get_result();

          if($Prequeryrequest->num_rows > 0){
            $arrData["friend_request"] = true;
          }

          ////////////////////////////////////////////////////////////////////////////////////////////

          $FaarRow[] = $arrData;

          $Result['total_found'] += 1;
        }
        $Result['data'] = $FaarRow; 
      }
      else{
        $Result['message'] = 'No Match Group';
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