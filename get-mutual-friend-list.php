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

    $error = false;

    if($mysqli){
      $myid = $_POST['user_id'];

      $sqlPreAll = $mysqli->prepare("SELECT friend_id FROM friend WHERE user_id = ? AND request_action IN ('Accept','Block') AND Status <> 'Delete' ");
      $sqlPreAll->bind_param("i",$myid);
      $sqlPreAll->execute();
      $queryPreALL  =   $sqlPreAll->get_result();

      $arrFr = array();
      while($RowPreAll = $queryPreALL->fetch_assoc()){
        $arrFr[] = $RowPreAll['friend_id'];
      }

      $sqlPreReAll = $mysqli->prepare("SELECT user_id FROM friend WHERE friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
      $sqlPreReAll->bind_param("i",$myid);
      $sqlPreReAll->execute();
      $queryPreReALL  =   $sqlPreReAll->get_result();
      
      while($RowPreReAll = $queryPreReALL->fetch_assoc()){
        $arrFr[] = $RowPreReAll['user_id'];
      }

      array_push($arrFr,$myid);

      $arrFrF = "(".implode(",",$arrFr).")";

      $sqlAll = $mysqli->prepare("SELECT * FROM user WHERE id NOT IN ".$arrFrF." AND Status <> 'Delete' ORDER BY RAND() ");
      $sqlAll->bind_param("i",$arrFrF);
      $sqlAll->execute();
      $queryALL  =   $sqlAll->get_result();

      while($RowAll = $queryALL->fetch_assoc()){
        $arrData["id"] = $RowAll["id"];
        $arrData["facebook_id"] = $RowAll["facebook_id"];
        $arrData["gmail_id"] = $RowAll["gmail_id"];
        $arrData["apple_id"] = $RowAll["apple_id"];
        $arrData["access_token"] = $RowAll["access_token"];
        $arrData["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$RowAll["avatar"];
        $arrData["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$RowAll["cover"];
        $arrData["username"] = $RowAll["username"];
        $arrData["first_name"] = $RowAll["first_name"];
        $arrData["last_name"] = $RowAll["last_name"];
        $arrData["gender"] = $RowAll["gender"];
        $arrData["gender_other"] = $RowAll["gender_other"];
        $arrData["birthdate"] = $RowAll["birthdate"];
        $arrData["telephone"] = $RowAll["telephone"];
        $arrData["email"] = $RowAll["email"];
        $arrData["facebook"] = $RowAll["facebook"];
        $arrData["line"] = $RowAll["line"];
        $arrData["address"] = $RowAll["address"];
        $arrData["country_id"] = $RowAll["country_id"];
        $arrData["country_other"] = $RowAll["country_other"];
        $arrData["province_id"] = $RowAll["province_id"];
        $arrData["province_other"] = $RowAll["province_other"];
        $arrData["occupation_id"] = $RowAll["occupation_id"];
        $arrData["work_address"] = $RowAll["work_address"];
        $arrData["about_me"] = $RowAll["about_me"];
        $arrData["status"] = $RowAll["status"];
        $arrData["createdate"] = $RowAll["createdate"];
        $arrData["create_ip"] = $RowAll["create_ip"];
        $arrData["updatedate"] = $RowAll["updatedate"];
        $arrData["update_ip"] = $RowAll["update_ip"];
        $arrData["lastlogin"] = $RowAll["lastlogin"];
        $arrData["lastlogin_ip"] = $RowAll["lastlogin_ip"];

        $Presql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
        $Presql1->bind_param("i", $RowAll["id"]);
        $Presql1->execute();
        $Prequery1  =   $Presql1->get_result();
        $PreRow1    =   $Prequery1->fetch_assoc();

        switch($ac_lang){
          case "en":
            $arrData["member_title"] = $PreRow1["title_en"];
            break;
          case "th":
            $arrData["member_title"] = $PreRow1["title_th"];
            break;
  
          default:
            $arrData["member_title"] = $PreRow1["title_th"];
        }

        $arrData["member_coin"] = $PreRow1["point"];
        $arrData["member_icon"] = SITE_URL."uploads/user_level/images/".$PreRow1["picture"];

        ////////////////////////////////////////////////////////////////////////////////////////////
        $arrData["friend"] = false;
        $arrData["following"] = false;
        $arrData["block"] = false;


        $preSql = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
        $preSql->bind_param("ii",$myid,$RowAll["id"]);
        $preSql->execute();
        $Prequery  =   $preSql->get_result();

        if($Prequery->num_rows > 0){
          $arrData["friend"] = true;
        }

        $preSqlBlock = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Block' AND Status <> 'Delete' ");
        $preSqlBlock->bind_param("ii",$myid,$RowAll["id"]);
        $preSqlBlock->execute();
        $PrequeryBlock  =   $preSqlBlock->get_result();

        if($PrequeryBlock->num_rows > 0){
          $arrData["block"] = true;
        }

        $preSql2 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
        $preSql2->bind_param("ii",$myid,$RowAll["id"]);
        $preSql2->execute();
        $Prequery2  =   $preSql2->get_result();

        if($Prequery2->num_rows > 0){
          $arrData["following"] = true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////

        $arrData["follower"] = false;
        $arrData["friend_request"] = false;
        $arrData["my_request"] = false;

        $preSqlfollow = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
        $preSqlfollow->bind_param("ii",$RowAll["id"],$myid);
        $preSqlfollow->execute();
        $Prequeryfollow  =   $preSqlfollow->get_result();

        if($Prequeryfollow->num_rows > 0){
          $arrData["follower"] = true;
        }

        $preSqlrequest = $mysqli->prepare("SELECT * FROM friend WHERE friend_id = ? AND user_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
        $preSqlrequest->bind_param("ii",$myid,$RowAll["id"]);
        $preSqlrequest->execute();
        $Prequeryrequest  =   $preSqlrequest->get_result();

        if($Prequeryrequest->num_rows > 0){
          $arrData["friend_request"] = true;
        }

        $preSqlmyrequest = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND request_action = 'Waiting' AND Status <> 'Delete' ");
        $preSqlmyrequest->bind_param("ii",$myid,$RowAll["id"]);
        $preSqlmyrequest->execute();
        $Prequerymyrequest  =   $preSqlmyrequest->get_result();

        if($Prequerymyrequest->num_rows > 0){
          $arrData["my_request"] = true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////

        $F_arr[] = $arrData;
        $Result['total_found'] += 1;
      }

      if(!$error){
        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[0];
        $Result['data'] = $F_arr;
      }
      else{
        $Result['message']      =   'Not Found Friend';
        $arrData = null;
      }

    }else{

      $Result['message']      = get_responseLang($ac_lang)[15];
    }    


}else{

  $Result['message']      = get_responseLang($ac_lang)[14];

}


echo json_encode($Result);

?>