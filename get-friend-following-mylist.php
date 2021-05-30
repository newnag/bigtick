<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0;
$Result['data']         =   null;


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $myid = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
      $sql->bind_param("i", $myid);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[0];

        while($Row = $query->fetch_assoc()){

          $sql1 = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status = 'Enable' ");
          $sql1->bind_param("i", $Row['follow_id']);
          $sql1->execute();
          $query1 = $sql1->get_result();
          
          $Row1 = $query1->fetch_assoc();
          $arrData["id"] = $Row1["id"];
          $arrData["facebook_id"] = $Row1["facebook_id"];
          $arrData["gmail_id"] = $Row1["gmail_id"];
          $arrData["apple_id"] = $Row1["apple_id"];
          $arrData["access_token"] = $Row1["access_token"];
          $arrData["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$Row1["avatar"];
          $arrData["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$Row1["cover"];
          $arrData["username"] = $Row1["username"];
          $arrData["first_name"] = $Row1["first_name"];
          $arrData["last_name"] = $Row1["last_name"];
          $arrData["gender"] = $Row1["gender"];
          $arrData["gender_other"] = $Row1["gender_other"];
          $arrData["birthdate"] = $Row1["birthdate"];
          $arrData["telephone"] = $Row1["telephone"];
          $arrData["email"] = $Row1["email"];
          $arrData["facebook"] = $Row1["facebook"];
          $arrData["line"] = $Row1["line"];
          $arrData["address"] = $Row1["address"];
          $arrData["country_id"] = $Row1["country_id"];
          $arrData["country_other"] = $Row1["country_other"];
          $arrData["province_id"] = $Row1["province_id"];
          $arrData["province_other"] = $Row1["province_other"];
          $arrData["occupation_id"] = $Row1["occupation_id"];
          $arrData["work_address"] = $Row1["work_address"];
          $arrData["about_me"] = $Row1["about_me"];
          $arrData["status"] = $Row1["status"];
          $arrData["createdate"] = $Row1["createdate"];
          $arrData["create_ip"] = $Row1["create_ip"];
          $arrData["updatedate"] = $Row1["updatedate"];
          $arrData["update_ip"] = $Row1["update_ip"];
          $arrData["lastlogin"] = $Row1["lastlogin"];
          $arrData["lastlogin_ip"] = $Row1["lastlogin_ip"];

          $sql2 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
          $sql2->bind_param("i", $Row1['id']);
          $sql2->execute();
          $query2  =   $sql2->get_result();
          $Row2    =   $query2->fetch_assoc();

          switch($ac_lang){
            case "en":
              $arrData["member_title"] = $Row2["title_en"];
              break;
            case "th":
              $arrData["member_title"] = $Row2["title_th"];
              break;
    
            default:
              $arrData["member_title"] = $Row2["title_th"];
          }
          
          $arrData["member_coin"] = $Row2["point"];
          $arrData["member_icon"] = SITE_URL."uploads/user_level/images/".$Row2["picture"];

          ////////////////////////////////////////////////////////////////////////////////////////////
          $arrData["friend"] = false;
          $arrData["following"] = false;
          $arrData["block"] = false;


          $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $preSql->bind_param("ii",$myid,$Row1["id"]);
          $preSql->execute();
          $Prequery  =   $preSql->get_result();

          if($Prequery->num_rows > 0){
            $arrData["friend"] = true;
          }

          $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND Status <> 'Delete' ");
          $preSqlBlock->bind_param("ii",$myid,$Row1["id"]);
          $preSqlBlock->execute();
          $PrequeryBlock  =   $preSqlBlock->get_result();
          $PreRow    =   $PrequeryBlock->fetch_assoc();

          if($PreRow['request_action'] == 'Block'){
            $arrData["block"] = true;
          }

          $preSql1 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSql1->bind_param("ii",$myid,$Row1["id"]);
          $preSql1->execute();
          $Prequery1  =   $preSql1->get_result();

          if($Prequery1->num_rows > 0){
            $arrData["following"] = true;
          }

          ////////////////////////////////////////////////////////////////////////////////////////////

          $arrData["follower"] = false;
          $arrData["friend_request"] = false;
          $arrData["my_request"] = false;

          $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSqlfollow->bind_param("ii",$Row1["id"],$myid);
          $preSqlfollow->execute();
          $Prequeryfollow  =   $preSqlfollow->get_result();

          if($Prequeryfollow->num_rows > 0){
            $arrData["follower"] = true;
          }

          $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE friend_id = ? AND user_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
          $preSqlrequest->bind_param("ii",$myid,$Row1["id"]);
          $preSqlrequest->execute();
          $Prequeryrequest  =   $preSqlrequest->get_result();

          if($Prequeryrequest->num_rows > 0){
            $arrData["friend_request"] = true;
          }

          $preSqlmyrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
          $preSqlmyrequest->bind_param("ii",$myid,$Row1["id"]);
          $preSqlmyrequest->execute();
          $Prequerymyrequest  =   $preSqlmyrequest->get_result();

          if($Prequerymyrequest->num_rows > 0){
            $arrData["my_request"] = true;
          }

          ////////////////////////////////////////////////////////////////////////////////////////////

          $FaarRow[] = $arrData;
          $Result['total_found'] += 1;
        }
        $Result['data'] = $FaarRow; 
      }
      else{
        $Result['message'] = get_responseLang($ac_lang)[25];
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