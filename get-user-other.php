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

if(!empty($_POST['user_id'])){

    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $user_id = $_POST['user_id'];
      $myid = $_POST['myid'];
      $show = false;

      $sql    =   $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status <> 'Delete' LIMIT 1");
      $sql->bind_param("i", $user_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $arrData = array();
        $Row    =   $query->fetch_assoc();

        $sqlPre = $mysqli->prepare("SELECT * FROM user_setting WHERE user_id = ? AND Status <> 'Delete' LIMIT 1");
        $sqlPre->bind_param("i", $Row["id"]);
        $sqlPre->execute();
        $queryPre  =   $sqlPre->get_result();
        $RowPre = $queryPre->fetch_assoc();

        if($RowPre['who_see_profile'] == 1){
          $show = true;
        }
        elseif($RowPre['who_see_profile'] == 2){
          $sqlPreFriend = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $sqlPreFriend->bind_param("i", $Row["id"]);
          $sqlPreFriend->execute();
          $queryPreFrien  =   $sqlPreFriend->get_result();
          while($RowPreFriend = $queryPreFrien->fetch_assoc()){
            $arrFriend[] = $RowPreFriend['friend_id'];
          }

          if(in_array($myid,$arrFriend)){
            $show = true;
          }
        }
        elseif($RowPre['who_see_profile'] == 3){
          $sqlPreFriend = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $sqlPreFriend->bind_param("i", $Row["id"]);
          $sqlPreFriend->execute();
          $queryPreFrien  =   $sqlPreFriend->get_result();
          while($RowPreFriend = $queryPreFrien->fetch_assoc()){
            $sqlPreFriendMu = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
            $sqlPreFriendMu->bind_param("i", $RowPreFriend["friend_id"]);
            $sqlPreFriendMu->execute();
            $queryPreFrienMu  =   $sqlPreFriendMu->get_result();
            while($RowPreFriendMu = $queryPreFrienMu->fetch_assoc()){
              $arrFriend[] = $RowPreFriendMu['friend_id'];
            }
          }

          if(in_array($myid,$arrFriend)){
            $show = true;
          }
        }
        elseif($RowPre['who_see_profile'] == 4){
          $sqlPreFollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
          $sqlPreFollow->bind_param("i", $Row["id"]);
          $sqlPreFollow->execute();
          $queryPreFollow =   $sqlPreFollow->get_result();
          while($RowPreFollow = $queryPreFollow->fetch_assoc()){
            $arrFollow[] = $RowPreFollow['follow_id'];
          }

          if(in_array($myid,$arrFollow)){
            $show = true;
          }
        }
        else{
          $show = false;
        }

        
        if($show){
          $Result['status']       =   'Success';
          $Result['message'] = get_responseLang($ac_lang)[0];
          $Result['total_found'] = sizeof($query);

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
          $sql1->bind_param("i", $user_id);
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

          //////////////////////////////////////////////////////////////////////////////////////////////

          $sqlFriend = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id <> ? AND request_action = 'Accept' AND Status <> 'Delete' ");
          $sqlFriend->bind_param("ii", $user_id,$myid);
          $sqlFriend->execute();
          $queryFriend  =   $sqlFriend->get_result();
          while($RowFriend = $queryFriend->fetch_assoc()){
            $userdata = get_user($RowFriend['friend_id']);

            $arrUser["id"] = $userdata["id"];
            $arrUser["facebook_id"] = $userdata["facebook_id"];
            $arrUser["gmail_id"] = $userdata["gmail_id"];
            $arrUser["apple_id"] = $userdata["apple_id"];
            $arrUser["access_token"] = $userdata["access_token"];
            $arrUser["avatar"] = $userdata["avatar"];
            $arrUser["cover"] = $userdata["cover"];
            $arrUser["username"] = $userdata["username"];
            $arrUser["first_name"] = $userdata["first_name"];
            $arrUser["last_name"] = $userdata["last_name"];
            $arrUser["gender"] = $userdata["gender"];
            $arrUser["gender_other"] = $userdata["gender_other"];
            $arrUser["birthdate"] = $userdata["birthdate"];
            $arrUser["telephone"] = $userdata["telephone"];
            $arrUser["email"] = $userdata["email"];
            $arrUser["facebook"] = $userdata["facebook"];
            $arrUser["line"] = $userdata["line"];
            $arrUser["address"] = $userdata["address"];
            $arrUser["country_id"] = $userdata["country_id"];
            $arrUser["country_other"] = $userdata["country_other"];
            $arrUser["province_id"] = $userdata["province_id"];
            $arrUser["province_other"] = $userdata["province_other"];
            $arrUser["occupation_id"] = $userdata["occupation_id"];
            $arrUser["work_address"] = $userdata["work_address"];
            $arrUser["about_me"] = $userdata["about_me"];
            $arrUser["status"] = $userdata["status"];
            $arrUser["createdate"] = $userdata["createdate"];
            $arrUser["create_ip"] = $userdata["create_ip"];
            $arrUser["updatedate"] = $userdata["updatedate"];
            $arrUser["update_ip"] = $userdata["update_ip"];
            $arrUser["lastlogin"] = $userdata["lastlogin"];
            $arrUser["lastlogin_ip"] = $userdata["lastlogin_ip"];

            $Presql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
            $Presql1->bind_param("i", $userdata["id"]);
            $Presql1->execute();
            $Prequery1  =   $Presql1->get_result();
            $PreRow1    =   $Prequery1->fetch_assoc();

            switch($ac_lang){
              case "en":
                $arrUser["member_title"] = $PreRow1["title_en"];
                break;
              case "th":
                $arrUser["member_title"] = $PreRow1["title_th"];
                break;
      
              default:
                $arrUser["member_title"] = $PreRow1["title_th"];
            }

            $arrUser["member_coin"] = $PreRow1["point"];
            $arrUser["member_icon"] = SITE_URL."uploads/user_level/images/".$PreRow1["picture"];

            ////////////////////////////////////////////////////////////////////////////////////////////
            $arrUser["friend"] = false;
            $arrUser["following"] = false;
            $arrUser["block"] = false;


            $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
            $preSql->bind_param("ii",$myid,$userdata["id"]);
            $preSql->execute();
            $Prequery  =   $preSql->get_result();

            if($Prequery->num_rows > 0){
              $arrUser["friend"] = true;
            }

            $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
            $preSqlBlock->bind_param("ii",$myid,$userdata["id"]);
            $preSqlBlock->execute();
            $PrequeryBlock  =   $preSqlBlock->get_result();

            if($PrequeryBlock->num_rows > 0){
              $arrUser["block"] = true;
            }

            $preSql2 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $preSql2->bind_param("ii",$myid,$userdata["id"]);
            $preSql2->execute();
            $Prequery2  =   $preSql2->get_result();

            if($Prequery2->num_rows > 0){
              $arrUser["following"] = true;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////

            $arrUser["follower"] = false;
            $arrUser["friend_request"] = false;

            $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $preSqlfollow->bind_param("ii",$userdata["id"],$myid);
            $preSqlfollow->execute();
            $Prequeryfollow  =   $preSqlfollow->get_result();

            if($Prequeryfollow->num_rows > 0){
              $arrUser["follower"] = true;
            }

            $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
            $preSqlrequest->bind_param("ii",$myid,$userdata["id"]);
            $preSqlrequest->execute();
            $Prequeryrequest  =   $preSqlrequest->get_result();

            if($Prequeryrequest->num_rows > 0){
              $arrUser["friend_request"] = true;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////

            $Arrsum_user[] = $arrUser;
            $Arr_User['number'] += 1;
            $Arr_User['friend_user'] = $Arrsum_user;
          }
          $arrData['friend'] = $Arr_User;

          ////////////////////////////////////////////////////////////////////////////////////////////

          $sqlFollowing = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id <> ? AND Status <> 'Delete' ");
          $sqlFollowing->bind_param("ii", $user_id,$myid);
          $sqlFollowing->execute();
          $queryFollowing  =   $sqlFollowing->get_result();
          while($RowFollowing = $queryFollowing->fetch_assoc()){
            $userdata_follow = get_user($RowFollowing['follow_id']);

            $arrUserFollow["id"] = $userdata_follow["id"];
            $arrUserFollow["facebook_id"] = $userdata_follow["facebook_id"];
            $arrUserFollow["gmail_id"] = $userdata_follow["gmail_id"];
            $arrUserFollow["apple_id"] = $userdata_follow["apple_id"];
            $arrUserFollow["access_token"] = $userdata_follow["access_token"];
            $arrUserFollow["avatar"] = $userdata_follow["avatar"];
            $arrUserFollow["cover"] = $userdata_follow["cover"];
            $arrUserFollow["username"] = $userdata_follow["username"];
            $arrUserFollow["first_name"] = $userdata_follow["first_name"];
            $arrUserFollow["last_name"] = $userdata_follow["last_name"];
            $arrUserFollow["gender"] = $userdata_follow["gender"];
            $arrUserFollow["gender_other"] = $userdata_follow["gender_other"];
            $arrUserFollow["birthdate"] = $userdata_follow["birthdate"];
            $arrUserFollow["telephone"] = $userdata_follow["telephone"];
            $arrUserFollow["email"] = $userdata_follow["email"];
            $arrUserFollow["facebook"] = $userdata_follow["facebook"];
            $arrUserFollow["line"] = $userdata_follow["line"];
            $arrUserFollow["address"] = $userdata_follow["address"];
            $arrUserFollow["country_id"] = $userdata_follow["country_id"];
            $arrUserFollow["country_other"] = $userdata_follow["country_other"];
            $arrUserFollow["province_id"] = $userdata_follow["province_id"];
            $arrUserFollow["province_other"] = $userdata_follow["province_other"];
            $arrUserFollow["occupation_id"] = $userdata_follow["occupation_id"];
            $arrUserFollow["work_address"] = $userdata_follow["work_address"];
            $arrUserFollow["about_me"] = $userdata_follow["about_me"];
            $arrUserFollow["status"] = $userdata_follow["status"];
            $arrUserFollow["createdate"] = $userdata_follow["createdate"];
            $arrUserFollow["create_ip"] = $userdata_follow["create_ip"];
            $arrUserFollow["updatedate"] = $userdata_follow["updatedate"];
            $arrUserFollow["update_ip"] = $userdata_follow["update_ip"];
            $arrUserFollow["lastlogin"] = $userdata_follow["lastlogin"];
            $arrUserFollow["lastlogin_ip"] = $userdata_follow["lastlogin_ip"];

            $Presql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
            $Presql1->bind_param("i", $userdata_follow["id"]);
            $Presql1->execute();
            $Prequery1  =   $Presql1->get_result();
            $PreRow1    =   $Prequery1->fetch_assoc();

            switch($ac_lang){
              case "en":
                $arrUserFollow["member_title"] = $PreRow1["title_en"];
                break;
              case "th":
                $arrUserFollow["member_title"] = $PreRow1["title_th"];
                break;
      
              default:
                $arrUserFollow["member_title"] = $PreRow1["title_th"];
            }

            $arrUserFollow["member_coin"] = $PreRow1["point"];
            $arrUserFollow["member_icon"] = SITE_URL."uploads/user_level/images/".$PreRow1["picture"];

            ////////////////////////////////////////////////////////////////////////////////////////////
            $arrUserFollow["friend"] = false;
            $arrUserFollow["following"] = false;
            $arrUserFollow["block"] = false;


            $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
            $preSql->bind_param("ii",$myid,$userdata_follow["id"]);
            $preSql->execute();
            $Prequery  =   $preSql->get_result();

            if($Prequery->num_rows > 0){
              $arrUserFollow["friend"] = true;
            }

            $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
            $preSqlBlock->bind_param("ii",$myid,$userdata_follow["id"]);
            $preSqlBlock->execute();
            $PrequeryBlock  =   $preSqlBlock->get_result();

            if($PrequeryBlock->num_rows > 0){
              $arrUserFollow["block"] = true;
            }

            $preSql2 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $preSql2->bind_param("ii",$myid,$userdata_follow["id"]);
            $preSql2->execute();
            $Prequery2  =   $preSql2->get_result();

            if($Prequery2->num_rows > 0){
              $arrUserFollow["following"] = true;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////

            $arrUserFollow["follower"] = false;
            $arrUserFollow["friend_request"] = false;

            $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $preSqlfollow->bind_param("ii",$userdata_follow["id"],$myid);
            $preSqlfollow->execute();
            $Prequeryfollow  =   $preSqlfollow->get_result();

            if($Prequeryfollow->num_rows > 0){
              $arrUserFollow["follower"] = true;
            }

            $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
            $preSqlrequest->bind_param("ii",$myid,$userdata_follow["id"]);
            $preSqlrequest->execute();
            $Prequeryrequest  =   $preSqlrequest->get_result();

            if($Prequeryrequest->num_rows > 0){
              $arrUserFollow["friend_request"] = true;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////

            $ArrsumF_user[] = $arrUserFollow;
            $Arr_Following['number'] += 1;
            $Arr_Following['follow_user'] = $ArrsumF_user;
          }
          $arrData['following'] = $Arr_Following;


          ////////////////////////////////////////////////////////////////////////////////////////////

          $sqlFollower = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id <> ? AND follow_id = ? AND Status <> 'Delete' ");
          $sqlFollower->bind_param("ii",$myid,$user_id);
          $sqlFollower->execute();
          $queryFollower  =   $sqlFollower->get_result();
          while($RowFollower = $queryFollower->fetch_assoc()){
            $userdata_follower = get_user($RowFollower['user_id']);

            $arrUserFollower["id"] = $userdata_follower["id"];
            $arrUserFollower["facebook_id"] = $userdata_follower["facebook_id"];
            $arrUserFollower["gmail_id"] = $userdata_follower["gmail_id"];
            $arrUserFollower["apple_id"] = $userdata_follower["apple_id"];
            $arrUserFollower["access_token"] = $userdata_follower["access_token"];
            $arrUserFollower["avatar"] = $userdata_follower["avatar"];
            $arrUserFollower["cover"] = $userdata_follower["cover"];
            $arrUserFollower["username"] = $userdata_follower["username"];
            $arrUserFollower["first_name"] = $userdata_follower["first_name"];
            $arrUserFollower["last_name"] = $userdata_follower["last_name"];
            $arrUserFollower["gender"] = $userdata_follower["gender"];
            $arrUserFollower["gender_other"] = $userdata_follower["gender_other"];
            $arrUserFollower["birthdate"] = $userdata_follower["birthdate"];
            $arrUserFollower["telephone"] = $userdata_follower["telephone"];
            $arrUserFollower["email"] = $userdata_follower["email"];
            $arrUserFollower["facebook"] = $userdata_follower["facebook"];
            $arrUserFollower["line"] = $userdata_follower["line"];
            $arrUserFollower["address"] = $userdata_follower["address"];
            $arrUserFollower["country_id"] = $userdata_follower["country_id"];
            $arrUserFollower["country_other"] = $userdata_follower["country_other"];
            $arrUserFollower["province_id"] = $userdata_follower["province_id"];
            $arrUserFollower["province_other"] = $userdata_follower["province_other"];
            $arrUserFollower["occupation_id"] = $userdata_follower["occupation_id"];
            $arrUserFollower["work_address"] = $userdata_follower["work_address"];
            $arrUserFollower["about_me"] = $userdata_follower["about_me"];
            $arrUserFollower["status"] = $userdata_follower["status"];
            $arrUserFollower["createdate"] = $userdata_follower["createdate"];
            $arrUserFollower["create_ip"] = $userdata_follower["create_ip"];
            $arrUserFollower["updatedate"] = $userdata_follower["updatedate"];
            $arrUserFollower["update_ip"] = $userdata_follower["update_ip"];
            $arrUserFollower["lastlogin"] = $userdata_follower["lastlogin"];
            $arrUserFollower["lastlogin_ip"] = $userdata_follower["lastlogin_ip"];

            $Presql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
            $Presql1->bind_param("i", $userdata_follower["id"]);
            $Presql1->execute();
            $Prequery1  =   $Presql1->get_result();
            $PreRow1    =   $Prequery1->fetch_assoc();

            switch($ac_lang){
              case "en":
                $arrUserFollower["member_title"] = $PreRow1["title_en"];
                break;
              case "th":
                $arrUserFollower["member_title"] = $PreRow1["title_th"];
                break;
      
              default:
                $arrUserFollower["member_title"] = $PreRow1["title_th"];
            }

            $arrUserFollower["member_coin"] = $PreRow1["point"];
            $arrUserFollower["member_icon"] = SITE_URL."uploads/user_level/images/".$PreRow1["picture"];

            ////////////////////////////////////////////////////////////////////////////////////////////
            $arrUserFollower["friend"] = false;
            $arrUserFollower["following"] = false;
            $arrUserFollower["block"] = false;


            $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
            $preSql->bind_param("ii",$myid,$userdata_follower["id"]);
            $preSql->execute();
            $Prequery  =   $preSql->get_result();

            if($Prequery->num_rows > 0){
              $arrUserFollower["friend"] = true;
            }

            $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
            $preSqlBlock->bind_param("ii",$myid,$userdata_follower["id"]);
            $preSqlBlock->execute();
            $PrequeryBlock  =   $preSqlBlock->get_result();

            if($PrequeryBlock->num_rows > 0){
              $arrUserFollower["block"] = true;
            }

            $preSql2 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $preSql2->bind_param("ii",$myid,$userdata_follower["id"]);
            $preSql2->execute();
            $Prequery2  =   $preSql2->get_result();

            if($Prequery2->num_rows > 0){
              $arrUserFollower["following"] = true;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////

            $arrUserFollower["follower"] = false;
            $arrUserFollower["friend_request"] = false;

            $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
            $preSqlfollow->bind_param("ii",$userdata_follower["id"],$myid);
            $preSqlfollow->execute();
            $Prequeryfollow  =   $preSqlfollow->get_result();

            if($Prequeryfollow->num_rows > 0){
              $arrUserFollower["follower"] = true;
            }

            $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
            $preSqlrequest->bind_param("ii",$myid,$userdata_follower["id"]);
            $preSqlrequest->execute();
            $Prequeryrequest  =   $preSqlrequest->get_result();

            if($Prequeryrequest->num_rows > 0){
              $arrUserFollower["friend_request"] = true;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////

            $ArrsumFW_user[] = $arrUserFollower;
            $Arr_Follower['number'] += 1;
            $Arr_Follower['follow_user'] = $ArrsumFW_user;
          }
          $arrData['follower'] = $Arr_Follower;


          ////////////////////////////////////////////////////////////////////////////////////////////

          $Result['data'] = $arrData;
        }
        else{
          $Result['message']      = get_responseLang($ac_lang)[25];
        }
        
      }
      else{
        $Result['message']      = get_responseLang($ac_lang)[13];
      }
      

    }else{

            $Result['message']      = get_responseLang($ac_lang)[15];
    }    


}else{

    $Result['message']      = get_responseLang($ac_lang)[14];

}


echo json_encode($Result);

?>