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


if(!empty($_POST['group_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $group_id = $_POST['group_id'];
      $myid = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? AND Status <> 'Delete' ");
      $sql->bind_param('i',$group_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = get_responseLang($ac_lang)[0];

        while($Row = $query->fetch_assoc()){
          $arrRow = array();

          $arrRow["id"] = $Row["id"];
          $arrRow["group_name"] = $Row["group_name"];
          $arrRow["picture"] = SITE_URL."uploads/group/images/".$Row["picture"];
          $arrRow["cover"] = SITE_URL."uploads/group/cover/".$Row["cover_pic"];
          $arrRow["public_status"] = $Row["public_status"];

          $sqlnummem = $mysqli->prepare("SELECT COUNT(id) FROM group_member WHERE group_id = ? AND request_status IN ('Approve','Accept') AND Status <> 'Delete' ");
          $sqlnummem->bind_param('i',$Row["id"]);
          $sqlnummem->execute();
          $querynummem  =   $sqlnummem->get_result();
          $Row_nummem = $querynummem->fetch_assoc();

          $arrRow["member"] = $Row_nummem["COUNT(id)"];
          $arrRow["createdate"] = $Row["CreateDate"];

          ////////////////////////////////////////////////////////////////////////////

          $sqlInter = $mysqli->prepare("SELECT inter.* FROM interest_list as inter 
                                        JOIN group_interest as ginter ON ginter.interest_id = inter.id
                                        WHERE ginter.group_id = ? ");
          $sqlInter->bind_param("i", $Row['id']);
          $sqlInter->execute();
          $queryInter  =   $sqlInter->get_result();  
          while($RowInter =  $queryInter->fetch_assoc()){
            $arrInter['id'] = $RowInter['id'];
            switch($ac_lang){
              case "en":
                $arrInter['name'] = $RowInter['name_en'];
                break;
              case "th":
                $arrInter['name'] = $RowInter['name_th'];
                break;
      
              default:
              $arrInter['name'] = $RowInter['name_th'];
            }

            $data_inter[] = $arrInter;
          }
             
          $arrRow["Interest"] = $data_inter;
          
          ////////////////////////////////////////////////////////////////////////////

          $sql2 = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND Status <> 'Delete' ");
          $sql2->bind_param("i", $Row['owner']);
          $sql2->execute();
          $query2  =   $sql2->get_result();
          $Row2 = $query2->fetch_assoc();

          $arrData["id"] = $Row2["id"];
          $arrData["facebook_id"] = $Row2["facebook_id"];
          $arrData["gmail_id"] = $Row2["gmail_id"];
          $arrData["apple_id"] = $Row2["apple_id"];
          $arrData["access_token"] = $Row2["access_token"];
          $arrData["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$Row2["avatar"];
          $arrData["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$Row2["cover"];
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

          $arrRow['owner'] = $arrData;

          //////////////////////////////////////////////////////////////////////////

          $sql3 = $mysqli->prepare("SELECT * FROM group_member WHERE group_id = ? AND user_id = ? AND Status <> 'Delete' ");
          $sql3->bind_param("ii",$Row['id'],$myid);
          $sql3->execute();
          $query3  =   $sql3->get_result();

          if($query3->num_rows > 0){
            $Row3 = $query3->fetch_assoc();

            if($Row3['request_status'] == 'Approve' || $Row3['request_status'] == 'Accept'){
              $arrRow['in_group'] = true;
            }
            else{
              $arrRow['in_group'] = false;
            }
          }
          else{
            $arrRow['in_group'] = false;
          }


          $FaarRow[] = $arrRow;

          $Result['total_found'] += 1;
        }
        $Result['data'] = $arrRow; 
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