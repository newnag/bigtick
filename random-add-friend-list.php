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
$Result['total_found']      =   0;
$Result['data']         =   null;


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];

      if(!empty($_POST['after_id'])){
        $afterid = intval($_POST['after_postid']);
        $arrNotID = array();

        $preqsl = $mysqli->prepare("SELECT user_id FROM friend WHERE friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
        $preqsl->bind_param("i", $myid);
        $preqsl->execute();
        $prequery  =   $preqsl->get_result();

        while($PreRow = $prequery->fetch_assoc()){
          $arrNotID[] = $PreRow['user_id'];
        }

        $preblock = $mysqli->prepare("SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
        $preblock->bind_param("i", $myid);
        $preblock->execute();
        $pre_blockquery  =   $preblock->get_result();

        while($PreBlockRow = $pre_blockquery->fetch_assoc()){
          $arrNotID[] = $PreBlockRow['friend_id'];
        }

        array_push($arrNotID,$myid);

        $arrNotIDF = "(".implode(",",$arrNotID).")";

        $sql = $mysqli->prepare("SELECT * FROM user WHERE id NOT IN ".$arrNotIDF." LIMIT 20 OFFSET ".$afterid." ");
        $sql->execute();
        $query  =   $sql->get_result();

        if($query->num_rows>0){ 
          while($Row = $query->fetch_assoc()){
            if($query->num_rows>0){
              $arrData = array();
      
              $arrData["id"] = $Row["id"];
              $arrData["facebook_id"] = $Row["facebook_id"];
              $arrData["gmail_id"] = $Row["gmail_id"];
              $arrData["apple_id"] = $Row["apple_id"];
              $arrData["access_token"] = $Row["access_token"];
              $arrData["avatar"] = SITE_URL."uploads/user/images/".$Row["avatar"];
              $arrData["cover"] = SITE_URL."uploads/user/cover/".$Row["cover"];
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
      
              $sql2 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
              $sql2->bind_param("i", $Row['id']);
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

              $arrData["follower"] = false;
              $arrData["friend_request"] = false;
              $arrData["my_request"] = false;

              $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
              $preSqlfollow->bind_param("ii",$Row["id"],$myid);
              $preSqlfollow->execute();
              $Prequeryfollow  =   $preSqlfollow->get_result();

              if($Prequeryfollow->num_rows > 0){
                $arrData["follower"] = true;
              }

              $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
              $preSqlrequest->bind_param("ii",$myid,$Row["id"]);
              $preSqlrequest->execute();
              $Prequeryrequest  =   $preSqlrequest->get_result();

              if($Prequeryrequest->num_rows > 0){
                $arrData["friend_request"] = true;
              }

              $preSqlmyrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
              $preSqlmyrequest->bind_param("ii",$myid,$Row["id"]);
              $preSqlmyrequest->execute();
              $Prequerymyrequest  =   $preSqlmyrequest->get_result();

              if($Prequerymyrequest->num_rows > 0){
                $arrData["my_request"] = true;
              }

              ////////////////////////////////////////////////////////////////////////////////////////////
  
              $FaarRow[] = $arrData;
              
            }
          }
          
          $Result['status'] = 'Success';
          $Result['message'] = 'Get Data Success';
          $Result['data'] = $FaarRow; 
          
        }
        else{
          $Result['message']      = 'Not Found Friend';
        }
      }
      else{
        $arrNotID = array();

        $preqsl = $mysqli->prepare("SELECT user_id FROM friend WHERE friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
        $preqsl->bind_param("i", $myid);
        $preqsl->execute();
        $prequery  =   $preqsl->get_result();

        while($PreRow = $prequery->fetch_assoc()){
          $arrNotID[] = $PreRow['user_id'];
        }

        $preblock = $mysqli->prepare("SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
        $preblock->bind_param("i", $myid);
        $preblock->execute();
        $pre_blockquery  =   $preblock->get_result();

        while($PreBlockRow = $pre_blockquery->fetch_assoc()){
          $arrNotID[] = $PreBlockRow['friend_id'];
        }

        array_push($arrNotID,$myid);

        $arrNotIDF = "(".implode(",",$arrNotID).")";

        $sql = $mysqli->prepare("SELECT * FROM user WHERE id NOT IN ".$arrNotIDF." AND Status = 'Enable' ");
        $sql->execute();
        $query  =   $sql->get_result();
  
        if($query->num_rows>0){ 
          while($Row = $query->fetch_assoc()){
            if($query->num_rows>0){
              $arrData = array();
      
              $arrData["id"] = $Row["id"];
              $arrData["facebook_id"] = $Row["facebook_id"];
              $arrData["gmail_id"] = $Row["gmail_id"];
              $arrData["apple_id"] = $Row["apple_id"];
              $arrData["access_token"] = $Row["access_token"];
              $arrData["avatar"] = SITE_URL."uploads/user/images/".$Row["avatar"];
              $arrData["cover"] = SITE_URL."uploads/user/cover/".$Row["cover"];
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
      
              $sql2 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
              $sql2->bind_param("i", $Row['id']);
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
              $preSql->bind_param("ii",$myid,$Row["id"]);
              $preSql->execute();
              $Prequery  =   $preSql->get_result();

              if($Prequery->num_rows > 0){
                $arrData["friend"] = true;
              }

              $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND Status <> 'Delete' ");
              $preSqlBlock->bind_param("ii",$myid,$Row["id"]);
              $preSqlBlock->execute();
              $PrequeryBlock  =   $preSqlBlock->get_result();
              $PreRow1    =   $PrequeryBlock->fetch_assoc();

              if($PreRow1['request_action'] == 'Block'){
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

              $arrData["follower"] = false;
              $arrData["friend_request"] = false;
              $arrData["my_request"] = false;

              $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
              $preSqlfollow->bind_param("ii",$Row["id"],$myid);
              $preSqlfollow->execute();
              $Prequeryfollow  =   $preSqlfollow->get_result();

              if($Prequeryfollow->num_rows > 0){
                $arrData["follower"] = true;
              }

              $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE friend_id = ? AND user_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
              $preSqlrequest->bind_param("ii",$myid,$Row["id"]);
              $preSqlrequest->execute();
              $Prequeryrequest  =   $preSqlrequest->get_result();

              if($Prequeryrequest->num_rows > 0){
                $arrData["friend_request"] = true;
              }

              $preSqlmyrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
              $preSqlmyrequest->bind_param("ii",$myid,$Row["id"]);
              $preSqlmyrequest->execute();
              $Prequerymyrequest  =   $preSqlmyrequest->get_result();

              if($Prequerymyrequest->num_rows > 0){
                $arrData["my_request"] = true;
              }

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

              ////////////////////////////////////////////////////////////////////////////////////////////

              $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
              $sql_publisher_friend->bind_param("i",$Row["id"]);
              $sql_publisher_friend->execute();
              $query_publisher_friend = $sql_publisher_friend->get_result();
              $Row_publisher_friend = $query_publisher_friend->fetch_assoc();

              $arrDataUser['friend_user'] = $Row_publisher_friend['id'];

              ///////////////////////////////////////////////////////////////////////////////////

              $sql_publisher_friend_mutual = $mysqli->prepare("SELECT COUNT(id) FROM friend WHERE user_id IN (SELECT friend_id FROM `friend` WHERE user_id IN (SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id <> ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
              $sql_publisher_friend_mutual->bind_param("iii",$Row["id"],$Row["id"],$Row["id"]);
              $sql_publisher_friend_mutual->execute();
              $query_publisher_friend_mutual = $sql_publisher_friend_mutual->get_result();
              $Row_publisher_friend_mutual = $query_publisher_friend_mutual->fetch_assoc();

              $arrDataUser['friend_mutual_user'] = $Row_publisher_friend_mutual['COUNT(id)'];

              ///////////////////////////////////////////////////////////////////////////////////
        
              $FaarRow[] = $arrData;
              
            }
          }
          
          $Result['status'] = 'Success';
          $Result['message'] = 'Get Data Success';
          $Result['data'] = $FaarRow; 
          
        }
        else{
          $Result['message']      = 'Not Found Friend';
        }
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