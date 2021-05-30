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

      $arr_ingroup = array();

      $Presql_inGroup = $mysqli->prepare("SELECT * FROM group_member WHERE user_id = ? AND request_status IN ('Approve','Accept') AND Status <> 'Delete'");
      $Presql_inGroup->bind_param("i", $myid);
      $Presql_inGroup->execute();
      $Prequery_ingroup = $Presql_inGroup->get_result();
      while($RowPre_ingroup = $Prequery_ingroup->fetch_assoc()){
        $arr_ingroup[] = $RowPre_ingroup['group_id'];
      }    

      $Presql = $mysqli->prepare("SELECT * FROM user_interest WHERE user_id = ? ");
      $Presql->bind_param("i", $myid);
      $Presql->execute();
      $Prequery  =   $Presql->get_result();

      if($Prequery->num_rows > 0){
        $arr_inter = array();

        while($PreRow = $Prequery->fetch_assoc()){
          array_push($arr_inter,$PreRow['interest_id']);
        }
        
        $arr_interest = "(".implode(",",$arr_inter).")";

        ///////////////////////////////////////////////////////////////////

        $sql = $mysqli->prepare("SELECT * FROM group_interest WHERE interest_id IN ".$arr_interest." AND Status <> 'Delete' GROUP BY group_id");
        $sql->execute();
        $query  =   $sql->get_result();

        $dataID_in_group = array();
        
        if($query->num_rows > 0){
          while($InsRow = $query->fetch_assoc()){
            $dataID_in_group[] = $InsRow['group_id'];
          }
        }

        $reatIDData = array_diff($dataID_in_group,$arr_ingroup);
        $ID_Data = "(".implode(',',$reatIDData).")";

        $sql1 = $mysqli->prepare("SELECT * FROM group_user WHERE id IN ".$ID_Data." AND Status <> 'Delete' ");
        $sql1->bind_param("i", $Row['group_id']);
        $sql1->execute();
        $query1  =   $sql1->get_result();
        while($Row1 = $query1->fetch_assoc()){
          $arr_data['id'] = $Row1['id'];
          $arr_data['group_name'] = $Row1['group_name']; 
          $arr_data['picture'] = SITE_URL."uploads/group/images/".$Row1['picture'];
          $arr_data['cover_pic'] = SITE_URL."uploads/group/cover/".$Row1['cover_pic'];
          $arr_data['public_status'] = $Row1['public_status'];
          $arr_data['CreateDate'] = $Row1['CreateDate'];
          $arr_data['Status'] = $Row1['Status'];

          ////////////////////////////////////////////////////////////////////////////

          $sql2 = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status <> 'Delete' ");
          $sql2->bind_param("i", $Row1['owner']);
          $sql2->execute();
          $query2  =   $sql2->get_result();
          $Row2 = $query2->fetch_assoc();

          $arrData["id"] = $Row2["id"];
          $arrData["facebook_id"] = $Row2["facebook_id"];
          $arrData["gmail_id"] = $Row2["gmail_id"];
          $arrData["apple_id"] = $Row2["apple_id"];
          $arrData["access_token"] = $Row2["access_token"];
          $arrData["avatar"] = $Row2["avatar"];
          $arrData["cover"] = $Row2["cover"];
          $arrData["username"] = $Row2["username"];
          $arrData["first_name"] = $Row2["first_name"];
          $arrData["last_name"] = $Row2["last_name"];
          $arrData["gender"] = $Row2["gender"];
          $arrData["gender_other"] = $Row2["gender_other"];
          $arrData["birthdate"] = $Row2["birthdate"];
          $arrData["telephone"] = $Row2["telephone"];
          $arrData["email"] = $Row2["email"];
          $arrData["facebook"] = $Row2["facebook"];
          $arrData["line"] = $Row2["line"];
          $arrData["address"] = $Row2["address"];
          $arrData["country_id"] = $Row2["country_id"];
          $arrData["country_other"] = $Row2["country_other"];
          $arrData["province_id"] = $Row2["province_id"];
          $arrData["province_other"] = $Row2["province_other"];
          $arrData["occupation_id"] = $Row2["occupation_id"];
          $arrData["work_address"] = $Row2["work_address"];
          $arrData["about_me"] = $Row2["about_me"];
          $arrData["status"] = $Row2["status"];
          $arrData["createdate"] = $Row2["createdate"];
          $arrData["create_ip"] = $Row2["create_ip"];
          $arrData["updatedate"] = $Row2["updatedate"];
          $arrData["update_ip"] = $Row2["update_ip"];
          $arrData["lastlogin"] = $Row2["lastlogin"];
          $arrData["lastlogin_ip"] = $Row2["lastlogin_ip"];

          $presql1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? LIMIT 1");
          $presql1->bind_param("i", $Row2['id']);
          $presql1->execute();
          $prequery1  =   $presql1->get_result();
          $preRow1    =   $prequery1->fetch_assoc();

          switch($ac_lang){
            case "en":
              $arrData["member_title"] = $preRow1["title_en"];
              break;
            case "th":
              $arrData["member_title"] = $preRow1["title_th"];
              break;
    
            default:
              $arrData["member_title"] = $preRow1["title_th"];
          }
          
          $arrData["member_coin"] = $preRow1["point"];
          $arrData["member_icon"] = SITE_URL."uploads/user_level/images/".$preRow1["picture"];

          $arrData["friend"] = false;
          $arrData["following"] = false;
          $arrData["block"] = false;

          $preSql2 = $mysqli->prepare("SELECT * FROM friend WHERE user_id = ? AND friend_id = ? AND Status <> 'Delete' ");
          $preSql2->bind_param("ii",$myid,$Row2["id"]);
          $preSql2->execute();
          $Prequery2  =   $preSql2->get_result();

          if($Prequery2->num_rows > 0){
            $arrData["friend"] = true;
          }

          $PreRow2    =   $Prequery2->fetch_assoc();

          if($PreRow2['request_action'] == 'Block'){
            $arrData["block"] = true;
          }

          $preSql3 = $mysqli->prepare("SELECT * FROM friend_follow WHERE user_id = ? AND follow_id = ? AND Status <> 'Delete' ");
          $preSql3->bind_param("ii",$myid,$Row2["id"]);
          $preSql3->execute();
          $Prequery3  =   $preSql3->get_result();

          if($Prequery3->num_rows > 0){
            $arrData["following"] = true;
          }


          $arr_data['owner'] = $arrData;

          //////////////////////////////////////////////////////////////////////////

          $sql_friend_request_group = $mysqli->prepare("SELECT * FROM group_member WHERE user_id = ? AND group_id = ? AND Status <> 'Delete'");
          $sql_friend_request_group->bind_param("ii",$myid,$Row1['id']);
          $sql_friend_request_group->execute();
          $Prequery_friend_request_group =   $sql_friend_request_group->get_result();
          $Row_friend_request_group = $Prequery_friend_request_group->fetch_assoc();

          if($Prequery_friend_request_group->num_rows > 0){
            if($Row_friend_request_group['request_status'] == "Inviting" || $Row_friend_request_group['request_status'] == "Requesting"){
              $arr_data["request_group_join"] = true;
              $arr_data["request_group_join_id"] = $Row1['id'];

              $arr_data["in_group"] = false;
              $arr_data["in_group_id"] = $Row1['id'];
            }
            elseif($Row_friend_request_group['request_status'] == "Approve" || $Row_friend_request_group['request_status'] == "Accept"){
              $arr_data["request_group_join"] = false;
              $arr_data["request_group_join_id"] = $Row1['id'];

              $arr_data["in_group"] = true;
              $arr_data["in_group_id"] = $Row1['id'];
            }
          }
          else{
            $arr_data["request_group_join"] = false;
            $arr_data["request_group_join_id"] = null;
            $arr_data["in_group"] = false;
            $arr_data["in_group_id"] = null;
          }

          $Result['total_found'] += 1;
          $Arr_Final[] = $arr_data;
        }

        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[0];
        $Result['data'] = $Arr_Final;
      }
      else{
        $Result['message']  = get_responseLang($ac_lang)[25];
      }

    }
    else{
      $Result['message']  = get_responseLang($ac_lang)[15];
    }    


}else{

    $Result['message'] = get_responseLang($ac_lang)[14];

}


echo json_encode($Result);

?>