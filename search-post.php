<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']      =   null;

if(!empty($_POST['keyword'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $keyword = "'%".$_POST['keyword']."%'";

      $sql = $mysqli->prepare("SELECT * FROM post WHERE title LIKE ".$keyword." AND Status <> 'Delete' ");
      // $sql->bind_param("i",$friend_id);
      $sql->execute();
      $query  =  $sql->get_result();

      if($query->num_rows > 0){
        while($Row = $query->fetch_assoc()){
          $Result['status'] = 'Success';
          $Result['message'] = 'Get Data Success';

          $arrRow["id"] = $Row["id"];
          $arrRow["title"] = $Row["title"];
          $arrRow["post_type"] = $Row["type_vote"];
          $arrRow["location"] = $Row["pin_map"];
          $arrRow["post_style"] = $Row["type_create_post"];

          $sqllike = $mysqli->prepare("SELECT COUNT(user_like) as user_like,COUNT(user_comment) as user_comment,COUNT(user_share) as user_share FROM post_comment WHERE post_id = ? ");
          $sqllike->bind_param("i",$Row["id"]);
          $sqllike->execute();
          $queryLike = $sqllike->get_result();
          $RowLike = $queryLike->fetch_assoc();

          $arrRow["likes"] = $RowLike["user_like"];
          $arrRow["comments"] = $RowLike["user_comment"];
          $arrRow["shares"] = $RowLike["user_share"];

          if($Row["type_vote"] == 'vote'){
            $sql1 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? ");
            $sql1->bind_param("i",$Row["id"]);
            $sql1->execute();
            $query1 = $sql1->get_result();

            if($query1->num_rows>0){
              while($Row1 = $query1->fetch_assoc()){  
                $sql2 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? AND Status <> 'Delete' ");
                $sql2->bind_param("i",$Row["id"]);
                $sql2->execute();
                $query2 = $sql2->get_result();
                $Row2 = $query2->fetch_assoc();

                $sqlchoice_like = $mysqli->prepare("SELECT * FROM post_choice_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                $sqlchoice_like->bind_param("ii",$Row["id"],$myid);
                $sqlchoice_like->execute();
                $queryChoice = $sqlchoice_like->get_result();
                $RowChoice = $queryChoice->fetch_assoc();

                $choice = array();
                $myLike = $RowChoice['choice_like'];

                $exArr1 =  explode(".",$Row1["choice_1"]);
                if($exArr1[1]){
                  $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                  $choice[0]["text"] = '';
                }
                else{
                  $choice[0]["image"] = '';
                  $choice[0]["text"] = $Row1["choice_1"];
                }
                
                $choice[0]["title"] = $Row1["choice_1_descript"];
                if($Row1["choice_1_result"] > 0){
                  $choice[0]["vote"] = $Row1["choice_1_result"];
                }
                else{
                  $choice[0]["vote"] = 0;
                }

                if($Row2["choice1_like"] > 0){
                  $choice[0]["likes"] =  $Row2["choice1_like"];
                }
                else{
                  $choice[0]["likes"] = 0;
                }

                if($Row2["choice1_comment"] > 0){
                  $choice[0]["comments"] =  $Row2["choice1_comment"];
                }
                else{
                  $choice[0]["comments"] = 0;
                }

                if($Row2["choice1_share"] > 0){
                  $choice[0]["share"] =  $Row2["choice1_share"];
                }
                else{
                  $choice[0]["share"] = 0;
                }

                if($myLike === 0){
                  $choice[0]["ismyLike"] = true;
                }
                else{
                  $choice[0]["ismyLike"] = false;
                }

                $exArr2 =  explode(".",$Row1["choice_2"]);
                if($exArr2[1]){
                  $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                  $choice[1]["text"] = '';
                }
                else{
                  $choice[1]["text"] = $Row1["choice_2"];
                  $choice[1]["image"] = '';
                }

                $choice[1]["title"] = $Row1["choice_2_descript"];
                if($Row1["choice_2_result"] > 0){
                  $choice[1]["vote"] = $Row1["choice_2_result"];
                }
                else{
                  $choice[1]["vote"] = 0;
                }

                if($Row2["choice2_like"] > 0){
                  $choice[1]["likes"] =  $Row2["choice2_like"];
                }
                else{
                  $choice[1]["likes"] = 0;
                }

                if($Row2["choice2_comment"] > 0){
                  $choice[1]["comments"] =  $Row2["choice2_comment"];
                }
                else{
                  $choice[1]["comments"] = 0;
                }

                if($Row2["choice2_share"] > 0){
                  $choice[1]["share"] =  $Row2["choice2_share"];
                }
                else{
                  $choice[1]["share"] = 0;
                }

                if($myLike == 1){
                  $choice[1]["ismyLike"] = true;
                }
                else{
                  $choice[1]["ismyLike"] = false;
                }

                if($Row1["choice_3"] != ''){
                  $exArr3 =  explode(".",$Row1["choice_3"]);
                  if($exArr3[1]){
                    $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                    $choice[2]["text"] = '';
                  }
                  else{
                    $choice[2]["text"] = $Row1["choice_3"];
                    $choice[2]["image"] = '';
                  }
    
                  $choice[2]["title"] = $Row1["choice_3_descript"];
                  if($Row1["choice_3_result"] > 0){
                    $choice[2]["vote"] = $Row1["choice_3_result"];
                  }
                  else{
                    $choice[2]["vote"] = 0;
                  }

                  if($Row2["choice3_like"] > 0){
                    $choice[2]["likes"] =  $Row2["choice3_like"];
                  }
                  else{
                    $choice[2]["likes"] = 0;
                  }

                  if($Row2["choice3_comment"] > 0){
                    $choice[2]["comments"] =  $Row2["choice3_comment"];
                  }
                  else{
                    $choice[2]["comments"] = 0;
                  }

                  if($Row2["choice3_share"] > 0){
                    $choice[2]["share"] =  $Row2["choice3_share"];
                  }
                  else{
                    $choice[2]["share"] = 0;
                  }

                  if($RowChoice['choice_like'] == 2){
                    $choice[2]["ismyLike"] = true;
                  }
                  else{
                    $choice[2]["ismyLike"] = false;
                  }
                }

                $arrRow["date_start"] = $Row1["date_start"];
                $arrRow["time_start"] = $Row1["time_start"];
                $arrRow["date_end"] = $Row1["date_end"];
                $arrRow["time_end"] = $Row1["time_end"];

                ////////////////////////////////////////////////////////////////////////////
                $arrTime = array();
                $origin = new DateTime();

                $timeplus = "'".$Row1["date_start"]." ".$Row1["time_start"]."'";

                if(date('Y-m-d H:i:s') >= $Row1["date_end"]." ".$Row1["time_end"]){
                  $arrRow["post_status"] = "close";
                  $arrRow["post_time_remain"] = "";
                }
                elseif(date('Y-m-d H:i:s') >= $Row1["date_start"]." ".$Row1["time_start"]){
                  $arrRow["post_status"] = "open";
                  $PreTime = new DateTime($Row1["date_end"]);

                  $interval = $origin->diff($PreTime);
                  $Ftime = $interval->format('%a days %h Hours %i Min');

                  $arrRow["post_time_remain"] = $Ftime;
                }
                elseif(date('Y-m-d H:i:s') <= $Row1["date_start"]." ".$Row1["time_start"]){                   
                  $PreTime = new DateTime($Row1["date_start"]." ".$Row1["time_start"]);
                  
                  $interval = $origin->diff($PreTime);

                  $checktime = $interval->format('%H:%I');
                  

                  $Ftime = $interval->format('%a days %h Hours %i Min');

                  $prepareTime = new DateTime();
                  $minTime = $prepareTime->format('%H:%I');

                  //print_r($checktime);

                  if($checktime <= '00:59'){
                    $arrRow["post_status"] = "incoming";
                    $arrRow["post_time_remain"] = $checktime;
                  }
                  else{
                    $arrRow["post_status"] = "normal";
                    $arrRow["post_time_remain"] = $Ftime;
                  }

                }

                // $arrRow["post_status"] = $arrTime;
                ////////////////////////////////////////////////////////////////////////////
                
              }

              $arrRow["choice"] = $choice;
            }
            else{
              $arrRow["choice"] = null;
            }
          }
          elseif($Row["type_vote"] == 'quiz'){
            $sql1 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? ");
            $sql1->bind_param("i",$Row["id"]);
            $sql1->execute();
            $query1 = $sql1->get_result();

            if($query1->num_rows>0){
              while($Row1 = $query1->fetch_assoc()){  
                $sql2 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? ");
                $sql2->bind_param("i",$Row["id"]);
                $sql2->execute();
                $query2 = $sql2->get_result();
                $Row2 = $query2->fetch_assoc();

                $choice = array();

                $exArr1 =  explode(".",$Row1["choice_1"]);
                if($exArr1[1]){
                  $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                  $choice[0]["text"] = '';
                }
                else{
                  $choice[0]["image"] = '';
                  $choice[0]["text"] = $Row1["choice_1"];
                }
                
                $choice[0]["title"] = $Row1["choice_1_descript"];
                if($Row1["choice_1_result"] > 0){
                  $choice[0]["vote"] = $Row1["choice_1_result"];
                }
                else{
                  $choice[0]["vote"] = 0;
                }

                if($Row2["choice1_like"] > 0){
                  $choice[0]["likes"] =  $Row2["choice1_like"];
                }
                else{
                  $choice[0]["likes"] = 0;
                }

                if($Row2["choice1_comment"] > 0){
                  $choice[0]["comments"] =  $Row2["choice1_comment"];
                }
                else{
                  $choice[0]["comments"] = 0;
                }

                if($Row2["choice1_share"] > 0){
                  $choice[0]["share"] =  $Row2["choice1_share"];
                }
                else{
                  $choice[0]["share"] = 0;
                }

                if($myLike === 0){
                  $choice[0]["ismyLike"] = true;
                }
                else{
                  $choice[0]["ismyLike"] = false;
                }

                $exArr2 =  explode(".",$Row1["choice_2"]);
                if($exArr2[1]){
                  $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                  $choice[1]["text"] = '';
                }
                else{
                  $choice[1]["text"] = $Row1["choice_2"];
                  $choice[1]["image"] = '';
                }

                $choice[1]["title"] = $Row1["choice_2_descript"];
                if($Row1["choice_2_result"] > 0){
                  $choice[1]["vote"] = $Row1["choice_2_result"];
                }
                else{
                  $choice[1]["vote"] = 0;
                }

                if($Row2["choice2_like"] > 0){
                  $choice[1]["likes"] =  $Row2["choice2_like"];
                }
                else{
                  $choice[1]["likes"] = 0;
                }

                if($Row2["choice2_comment"] > 0){
                  $choice[1]["comments"] =  $Row2["choice2_comment"];
                }
                else{
                  $choice[1]["comments"] = 0;
                }

                if($Row2["choice2_share"] > 0){
                  $choice[1]["share"] =  $Row2["choice2_share"];
                }
                else{
                  $choice[1]["share"] = 0;
                }

                if($myLike == 1){
                  $choice[1]["ismyLike"] = true;
                }
                else{
                  $choice[1]["ismyLike"] = false;
                }

                if($Row1["choice_3"] != ''){
                  $exArr3 =  explode(".",$Row1["choice_3"]);
                  if($exArr3[1]){
                    $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                    $choice[2]["text"] = '';
                  }
                  else{
                    $choice[2]["text"] = $Row1["choice_3"];
                    $choice[2]["image"] = '';
                  }
    
                  $choice[2]["title"] = $Row1["choice_3_descript"];
                  if($Row1["choice_3_result"] > 0){
                    $choice[2]["vote"] = $Row1["choice_3_result"];
                  }
                  else{
                    $choice[2]["vote"] = 0;
                  }

                  if($Row2["choice3_like"] > 0){
                    $choice[2]["likes"] =  $Row2["choice3_like"];
                  }
                  else{
                    $choice[2]["likes"] = 0;
                  }

                  if($Row2["choice3_comment"] > 0){
                    $choice[2]["comments"] =  $Row2["choice3_comment"];
                  }
                  else{
                    $choice[2]["comments"] = 0;
                  }

                  if($Row2["choice3_share"] > 0){
                    $choice[2]["share"] =  $Row2["choice3_share"];
                  }
                  else{
                    $choice[2]["share"] = 0;
                  }
                }

                $arrRow["date_start"] = $Row1["date_start"];
                $arrRow["time_start"] = $Row1["time_start"];
                $arrRow["date_end"] = $Row1["date_end"];
                $arrRow["time_end"] = $Row1["time_end"];
                // $answer = $Row1["anwser"];

                if($Row1["anwser"] == 1){
                  $arrRow["answer"] = 0;
                }
                elseif($Row1["anwser"] == 2){
                  $arrRow["answer"] = 1;
                }
                elseif($Row1["anwser"] == 3){
                  $arrRow["answer"] = 2;
                }

                ////////////////////////////////////////////////////////////////////////////
                $arrTime = array();
                $origin = new DateTime();

                $timeplus = "'".$Row1["date_start"]." ".$Row1["time_start"]."'";

                if(date('Y-m-d H:i:s') >= $Row1["date_end"]." ".$Row1["time_end"]){
                  $arrRow["post_status"] = "close";
                  $arrRow["post_time_remain"] = "";
                }
                elseif(date('Y-m-d H:i:s') >= $Row1["date_start"]." ".$Row1["time_start"]){
                  $arrRow["post_status"] = "open";
                  $PreTime = new DateTime($Row1["date_end"]);

                  $interval = $origin->diff($PreTime);
                  $Ftime = $interval->format('%a days %h Hours %i Min');

                  $arrRow["post_time_remain"] = $Ftime;
                }
                elseif(date('Y-m-d H:i:s') <= $Row1["date_start"]." ".$Row1["time_start"]){                   
                  $PreTime = new DateTime($Row1["date_start"]." ".$Row1["time_start"]);
                  
                  $interval = $origin->diff($PreTime);

                  $checktime = $interval->format('%H:%I');
                  $minTime = $interval->format('%I:%S');

                  $Ftime = $interval->format('%a days %h Hours %i Min');

                  $prepareTime = new DateTime("00:59");
                  $prepatemin = $prepareTime->format('%H:%I');

                  if($checktime <= $prepareTime){
                    $arrRow["post_status"] = "incoming";
                    $arrRow["post_time_remain"] = $minTime;
                  }
                  else{
                    $arrRow["post_status"] = "waiting";
                    $arrRow["post_time_remain"] = $Ftime;
                  }

                }

                // $arrRow["post_status"] = $arrTime;
                ////////////////////////////////////////////////////////////////////////////
              }

              $arrRow["choice"] = $choice;
              
            }
            else{
              $arrRow["choice"] = null;
            }
          }
          else{
            $sqlpic = $mysqli->prepare("SELECT * FROM post_meta_novote WHERE post_id = ? ");
            $sqlpic->bind_param("i",$Row["id"]);
            $sqlpic->execute();
            $queryPic = $sqlpic->get_result();
            $RowPic = $queryPic->fetch_assoc();

            $choice = array();

            $choice[0]["image"] = SITE_URL."uploads/posts/images/".$RowPic["picture"];
            $choice[0]["text"] = "";
            $choice[0]["title"] = "";
            $choice[0]["vote"] = 0;
            $choice[0]["likes"] = 0;
            $choice[0]["comments"] = 0;
            $choice[0]["share"] = 0;

            $arrRow["choice"] = $choice;
            $arrRow["post_status"] = "normal";
            $arrRow["post_time_remain"] = "";
            $arrRow["createdate"] = $Row["CreateDate"];
            $arrRow["createip"] = $Row["CreateIP"];
          }

          $sqluser = $mysqli->prepare("SELECT * FROM user WHERE id = ? LIMIT 1");
          $sqluser->bind_param("i",$Row["user_id"]);
          $sqluser->execute();
          $queryUser = $sqluser->get_result();

          if($queryUser->num_rows>0){
            $RowUser = $queryUser->fetch_assoc();
            $arrDataUser = array();

            $arrDataUser["id"] = $RowUser["id"];
            $arrDataUser["facebook_id"] = $RowUser["facebook_id"];
            $arrDataUser["gmail_id"] = $RowUser["gmail_id"];
            $arrDataUser["apple_id"] = $RowUser["apple_id"];
            $arrDataUser["access_token"] = $RowUser["access_token"];
            $arrDataUser["avatar"] = $RowUser["avatar"];
            $arrDataUser["cover"] = $RowUser["cover"];
            $arrDataUser["username"] = $RowUser["username"];
            $arrDataUser["first_name"] = $RowUser["first_name"];
            $arrDataUser["last_name"] = $RowUser["last_name"];
            $arrDataUser["gender"] = $RowUser["gender"];
            $arrDataUser["gender_other"] = $RowUser["gender_other"];
            $arrDataUser["birthdate"] = $RowUser["birthdate"];
            $arrDataUser["telephone"] = $RowUser["telephone"];
            $arrDataUser["email"] = $RowUser["email"];
            $arrDataUser["facebook"] = $RowUser["facebook"];
            $arrDataUser["line"] = $RowUser["line"];
            $arrDataUser["address"] = $RowUser["address"];
            $arrDataUser["country_id"] = $RowUser["country_id"];
            $arrDataUser["country_other"] = $RowUser["country_other"];
            $arrDataUser["province_id"] = $RowUser["province_id"];
            $arrDataUser["province_other"] = $RowUser["province_other"];
            $arrDataUser["occupation_id"] = $RowUser["occupation_id"];
            $arrDataUser["work_address"] = $RowUser["work_address"];
            $arrDataUser["about_me"] = $RowUser["about_me"];
            $arrDataUser["status"] = $RowUser["status"];
            $arrDataUser["createdate"] = $RowUser["createdate"];
            $arrDataUser["create_ip"] = $RowUser["create_ip"];
            $arrDataUser["updatedate"] = $RowUser["updatedate"];
            $arrDataUser["update_ip"] = $RowUser["update_ip"];
            $arrDataUser["lastlogin"] = $RowUser["lastlogin"];
            $arrDataUser["lastlogin_ip"] = $RowUser["lastlogin_ip"];

            $sqlclass_user = $mysqli->prepare("SELECT a.class_name,a.point,b.icon FROM class_user as a JOIN class_table as b ON a.class_name = b.name WHERE user_id = ? ");
            $sqlclass_user->bind_param("i",$Row["user_id"]);
            $sqlclass_user->execute();
            $queryClass_User = $sqlclass_user->get_result();

            if($queryClass_User->num_rows>0){
              $RowClass_User = $queryClass_User->fetch_assoc();

              $arrDataUser["class_name"] = $RowClass_User["class_name"];
              $arrDataUser["point"] = $RowClass_User["point"];
              $arrDataUser["icon"] = $RowClass_User["icon"];
            }
            else{
              $arrDataUser['class_name'] = "";
              $arrDataUser['point'] = "";
              $arrDataUser['icon'] = "";
            }

            $arrRow["publisher"] = $arrDataUser;
          }
          else{
            $arrRow["publisher"] = null;
          }

          ///////////////////////////////////////////////////////////////////////////////////////////////
          $arrTypeTitle = explode(",",$Row["type_title"]);
          $ArrInterest = array();

          for($i=0;$i<sizeof($arrTypeTitle);$i++){
            $sqlTopic = $mysqli->prepare("SELECT * FROM interest_list WHERE id = ? ");
            $sqlTopic->bind_param("i",$arrTypeTitle[$i]);
            $sqlTopic->execute();
            $queryTopic = $sqlTopic->get_result();

            $ArrTopic = array();
            if($queryTopic->num_rows>0){
              $RowInterest = $queryTopic->fetch_assoc();

              $ArrTopic["id"] = $RowInterest["id"];
              $ArrTopic["name"] = $RowInterest["name"];

              $ArrInterest[] = $ArrTopic;
            }
          }
          if($ArrInterest == null){
            $arrRow["interest"] = null;
          }
          else{
            $arrRow["interest"] = $ArrInterest;
          }
          

          ///////////////////////////////////////////////////////////////////////////////////////////////

          $sqlSelect = $mysqli->prepare("SELECT * FROM post_meta_user WHERE user_id = ? AND post_id = ? LIMIT 1");
          $sqlSelect->bind_param("ii",$myid,$Row["id"]);
          $sqlSelect->execute();
          $querySelect = $sqlSelect->get_result();

          if($querySelect->num_rows>0){
            $RowSelect = $querySelect->fetch_assoc();
            $arrRow["select_choice"] = $RowSelect["selector"];
          }
          else{
            $arrRow["select_choice"] = '';
          }

          if($Row["user_tag"] != ''){
            $exArrUser_tag = explode(",",$Row["user_tag"]);
            $Array_usertag = array();

            for($i=0;$i<sizeof($exArrUser_tag);$i++){ 
              $array_userTag = array();

              $sqlUserTag = $mysqli->prepare("SELECT * FROM user WHERE id = ? ");
              $sqlUserTag->bind_param("i",$exArrUser_tag[$i]);
              $sqlUserTag->execute();
              $queryUserTag = $sqlUserTag->get_result();
              $RowUserTag = $queryUserTag->fetch_assoc();
              
              $array_userTag["id"] = $RowUserTag["id"];
              $array_userTag["username"] = $RowUserTag["username"];
              $array_userTag["first_name"] = $RowUserTag["first_name"];
              $array_userTag["last_name"] = $RowUserTag["last_name"];

              $Array_usertag[] = $array_userTag;
            }

            $arrRow["tagusers"] = $Array_usertag;
          }
          else{
            $arrRow["tagusers"] = null;
          }

          ///////////////////////////////////////////////////////////////////////////////////////////////

          if($Row["tag"] != ''){
            $arrTag = explode(",",$Row["tag"]);
            $ArrTagGroup = array();

            for($i=0;$i<sizeof($arrTag);$i++){
              $sqlTagGroup = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? ");
              $sqlTagGroup->bind_param("i",$arrTag[$i]);
              $sqlTagGroup->execute();
              $queryTagGroup = $sqlTagGroup->get_result();

              $ArrTag = array();
              $RowTag = $queryTagGroup->fetch_assoc();

              $ArrTag["id"] = $RowTag["id"];
              $ArrTag["group_name"] = $RowTag["group_name"];
              $ArrTag["picture"] = SITE_URL."uploads/group/images/".$RowTag["picture"];

              $ArrTagGroup[] = $ArrTag;
            }

            $arrRow["topic"] = $ArrTagGroup;
          }
          else{
            $arrRow["topic"] = null;
          }


          ///////////////////////////////////////////////////////////////////////////////////////////////

          $Result['total_found']++;

          $FaarRow[] = $arrRow;

          $Result['data'] = $FaarRow;
        }
      }
      else{
        $Result['message'] = 'Not Found Post';
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