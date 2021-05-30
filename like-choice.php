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

if(!empty($_POST['post_id']) && !empty($_POST['user_id'])){
  $mysqli = db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $myid = $_POST['user_id'];
    $post_id = $_POST['post_id'];
    $choice = intval($_POST['choice']);
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];

    $presql = $mysqli->prepare("SELECT * FROM post WHERE id = ? ");
    $presql->bind_param("i",$post_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $status = 'Enable';

        $presql2 = $mysqli->prepare("SELECT * FROM post_choice_meta_user WHERE post_id = ? AND user_id = ? AND choice_like = ? AND Status <> 'Delete' ");
        $presql2->bind_param("iii",$post_id,$myid,$choice);
        $presql2->execute();
        $prequery2 = $presql2->get_result();
  
        if($prequery2->num_rows == 0){
          $sql = $mysqli->prepare("INSERT INTO post_choice_meta_user(post_id,user_id,choice_like,Status)  VALUES(?,?,?,?)");
          $sql->bind_param("iiis",$post_id,$myid,$choice,$status);
  
          if($sql->execute()){
            $sql2 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? AND Status <> 'Delete' ");
            $sql2->bind_param("i",$post_id);
            $sql2->execute();
            $query2 = $sql2->get_result();
            
            if($query2->num_rows == 0){
              $sql3 = $mysqli->prepare("INSERT INTO post_choice_meta(post_id,CreateDate,CreateIP,Status)  VALUES(?,?,?,?)");
              $sql3->bind_param("isss",$post_id,$createdate,$create_ip,$status);
              if($sql3->execute()){
                $c_val = 1;

                if($choice == '0'){
                  $sql4 = $mysqli->prepare("UPDATE post_choice_meta SET choice1_like = ? WHERE post_id = ?");
                  $sql4->bind_param("ii",$c_val,$post_id);
                  $sql4->execute();
                }
                elseif($choice == '1'){
                  $sql4 = $mysqli->prepare("UPDATE post_choice_meta SET choice2_like = ? WHERE post_id = ?");
                  $sql4->bind_param("ii",$c_val,$post_id);
                  $sql4->execute();
                }
                elseif($choice == '2'){
                  $sql4 = $mysqli->prepare("UPDATE post_choice_meta SET choice3_like = ? WHERE post_id = ?");
                  $sql4->bind_param("ii",$c_val,$post_id);
                  $sql4->execute();
                }

                //////////////////////////////////////////////////////////////////////////////////////////////////////////

                $Result['status'] = 'Success';
                $Result['message'] = get_responseLang($ac_lang)[4];

                $sqlF1 = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
                $sqlF1->bind_param("i",$post_id);
                $sqlF1->execute();
                $queryF1  =   $sqlF1->get_result();

                if($queryF1->num_rows>0){
                  while($Row = $queryF1->fetch_assoc()){
                    $arrRow["id"] = $Row["id"];
                    $arrRow["title"] = $Row["title"];
                    $arrRow["post_type"] = $Row["type_vote"];
                    $arrRow["location"] = $Row["pin_map"];
                    $arrRow["post_style"] = $Row["type_create_post"];
                    $arrRow["post_status"] = $Row["post_status"];
                    $arrRow["vote_limit"] = $Row["limit_number_vote"];
                
                    $sqllike = $mysqli->prepare("SELECT user_like, user_comment, user_share FROM post_comment WHERE post_id = ? AND Status <> 'Delete' ");
                    $sqllike->bind_param("i",$Row["id"]);
                    $sqllike->execute();
                    $queryLike = $sqllike->get_result();
                
                    $countLike = 0;
                    $countComment = 0;
                    $countShare = 0;
                
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                
                    $sql_post_like = $mysqli->prepare("SELECT * FROM post_comment WHERE post_id = ? AND user_like = ? AND Status <> 'Delete' ");
                    $sql_post_like->bind_param("ii",$Row["id"],$myid);
                    $sql_post_like->execute();
                    $query_post_Like = $sql_post_like->get_result();
                
                    if($query_post_Like->num_rows > 0){
                      $arrRow["post_ismyLike"] = true;
                    }
                    else{
                      $arrRow["post_ismyLike"] = false;
                    }
                
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                
                    $sql_post_share = $mysqli->prepare("SELECT * FROM post_share WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
                    $sql_post_share->bind_param("ii",$Row["id"],$myid);
                    $sql_post_share->execute();
                    $query_post_share = $sql_post_share->get_result();
                
                    if($query_post_share->num_rows > 0){
                      $Rowshare = $query_post_share->fetch_assoc();
                
                      $arrRow["post_myshare"] = true;
                      $arrRow["type_share"] = $Rowshare['type_share'];
                    }
                    else{
                      $arrRow["post_myshare"] = false;
                      $arrRow["type_share"] = "";
                    }
                
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                
                    while($RowLike = $queryLike->fetch_assoc()){
                      if($RowLike["user_like"] > 0){
                        $countLike += 1;
                      }
                      if($RowLike["user_comment"] > 0){
                        $countComment += 1;
                      }
                      if($RowLike["user_share"] > 0){
                        $countShare += 1;
                      }
                    }
                
                    $arrRow["likes"] = $countLike;
                    $arrRow["comments"] = $countComment;
                    $arrRow["shares"] = $countShare;
                
                    /////////////////////////////////////////////////////////////////////////////////////////////////
                
                    $sql_pin = $mysqli->prepare("SELECT * FROM post_pin WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
                    $sql_pin->bind_param("ii",$Row["id"],$myid);
                    $sql_pin->execute();
                    $query_post_pin = $sql_pin->get_result();
                
                    if($query_post_pin->num_rows > 0){
                      $arrRow["pin"] = true;
                    }
                    else{
                      $arrRow["pin"] = false;
                    }
                
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                
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
                          while($RowChoice = $queryChoice->fetch_assoc()){
                            $myLike[] = $RowChoice['choice_like'];
                          }
                          
                          $choice = array();  
                
                          //////////////////////////////////////////////////////////////////////////
                
                          $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                          $sqlchoice_vote->bind_param("ii",$Row["id"],$myid);
                          $sqlchoice_vote->execute();
                          $queryChoiceVote = $sqlchoice_vote->get_result();
                          $RowChoiceVote = $queryChoiceVote->fetch_assoc();
                          
                          ////////////////////////////////////////////////////////////////////////// 
              
                          $checkTxt1 = false;
                          $checkTxt2 = false;
                          $checkTxt3 = false;
              
                          $sqltxt_Img1 = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                          $sqltxt_Img1->bind_param("i",$Row["id"]);
                          $sqltxt_Img1->execute();
                          $querytxt_Img1 = $sqltxt_Img1->get_result();
              
                          while($RowTxt_Img = $querytxt_Img1->fetch_assoc()){
                            $TxtImg[] = $RowTxt_Img['picture'];
                          }
                
                          if($Row1['choice_1_textImg'] != 0){
                            $checkTxt1 = true;
                          }
                          if($Row1['choice_2_textImg'] != 0){
                            $checkTxt2 = true;
                          }
                          if($Row1['choice_3_textImg'] != 0){
                            $checkTxt3 = true;
                          }
              
                          $exArr1 =  explode(".",$Row1["choice_1"]);
              
                          if($checkTxt1){
                            $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                            $choice[0]["text"] = '';
                            $choice[0]["type_choice"] = "text";
                          }
                          else{
                            if($exArr1[1]){
                              $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                              $choice[0]["text"] = '';
                              if($exArr1[1] == "mp4"){
                                $choice[0]["type_choice"] = "video";
                              }
                              else{
                                $choice[0]["type_choice"] = "picture";
                              }
                            }
                            else{
                              $choice[0]["image"] = '';
                              $choice[0]["text"] = '';
                              $choice[0]["type_choice"] = "";
                            }
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
              
                          $choice[0]["bg_color"] = $Row1["choice_1_bg"];
                          $choice[0]["txt_color"] = $Row1["choice_1_txt_color"];
              
                          $choice[0]["ismyLike"] = false;
                          foreach($myLike as $Islike){
                            if($Islike == "0"){
                              $choice[0]["ismyLike"] = true;
                            }
                          }
              
                          $choice[0]["ismyVote"] = false;
              
                          if($RowChoiceVote['selector'] == '0'){
                            $choice[0]["ismyVote"] = true;
                          }
              
                          if($Row1["choice_2"] != '' || $Row1['choice_2_textImg'] != 0){
                            $exArr2 =  explode(".",$Row1["choice_2"]);
              
                            if($checkTxt2){
                              $choice[1]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[1];
                              $choice[1]["text"] = '';
                              $choice[1]["type_choice"] = "text";
                            }
                            else{
                              if($exArr2[1]){
                                $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                                $choice[1]["text"] = '';
                                if($exArr2[1] == "mp4"){
                                  $choice[1]["type_choice"] = "video";
                                }
                                else{
                                  $choice[1]["type_choice"] = "picture";
                                }
                              }
                              else{
                                $choice[1]["text"] = '';
                                $choice[1]["image"] = '';
                                $choice[1]["type_choice"] = "";
                              }
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
              
                            $choice[1]["bg_color"] = $Row1["choice_2_bg"];
                            $choice[1]["txt_color"] = $Row1["choice_2_txt_color"];
              
                            $choice[1]["ismyLike"] = false;
                            foreach($myLike as $Islike){
                              if($Islike == "1"){
                                $choice[1]["ismyLike"] = true;
                              }
                            }
              
                            $choice[1]["ismyVote"] = false;
              
                            if($RowChoiceVote['selector'] == '1'){
                              $choice[1]["ismyVote"] = true;
                            }
                          }
              
                          if($Row1["choice_3"] != '' || $Row1['choice_3_textImg'] != 0){
                            $choice[2]["ismyVote"] = false;
              
                            $exArr3 =  explode(".",$Row1["choice_3"]);
              
                            if($checkTxt3){
                              $choice[2]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[2];
                              $choice[2]["text"] = '';
                              $choice[2]["type_choice"] = "text";
                            }
                            else{
                              if($exArr3[1]){
                                $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                                $choice[2]["text"] = '';
                                if($exArr3[1] == "mp4"){
                                  $choice[2]["type_choice"] = "video";
                                }
                                else{
                                  $choice[2]["type_choice"] = "picture";
                                }
                              }
                              else{
                                $choice[2]["text"] = $Row1["choice_3"];
                                $choice[2]["image"] = '';
                                $choice[2]["type_choice"] = "text";
                              }
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
              
                            $choice[2]["bg_color"] = $Row1["choice_3_bg"];
                            $choice[2]["txt_color"] = $Row1["choice_3_txt_color"];
              
                            $choice[2]["ismyLike"] = false;
                            foreach($myLike as $Islike){
                              if($Islike == "2"){
                                $choice[2]["ismyLike"] = true;
                              }
                            }
              
                            if($RowChoiceVote['selector'] == '2'){
                              $choice[2]["ismyVote"] = true;
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
                
                          //////////////////////////////////////////////////////////////////////////
                
                          $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                          $sqlchoice_vote->bind_param("ii",$Row["id"],$myid);
                          $sqlchoice_vote->execute();
                          $queryChoiceVote = $sqlchoice_vote->get_result();
                          
                          while($RowChoiceVote = $queryChoiceVote->fetch_assoc()){
                            $myLike[] = $RowChoiceVote['choice_like'];
                          }
                
                          //////////////////////////////////////////////////////////////////////////
                
                          $checkTxt1 = false;
                          $checkTxt2 = false;
                          $checkTxt3 = false;
              
                          $sqltxt_Img1 = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                          $sqltxt_Img1->bind_param("i",$Row["id"]);
                          $sqltxt_Img1->execute();
                          $querytxt_Img1 = $sqltxt_Img1->get_result();
              
                          while($RowTxt_Img = $querytxt_Img1->fetch_assoc()){
                            $TxtImg[] = $RowTxt_Img['picture'];
                          }
                
                          if($Row1['choice_1_textImg'] != 0){
                            $checkTxt1 = true;
                          }
                          if($Row1['choice_2_textImg'] != 0){
                            $checkTxt2 = true;
                          }
                          if($Row1['choice_3_textImg'] != 0){
                            $checkTxt3 = true;
                          }
              
                          $exArr1 =  explode(".",$Row1["choice_1"]);
              
                          if($checkTxt1){
                            $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                            $choice[0]["text"] = '';
                            $choice[0]["type_choice"] = "text";
                          }
                          else{
                            if($exArr1[1]){
                              $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                              $choice[0]["text"] = '';
                              if($exArr1[1] == "mp4"){
                                $choice[0]["type_choice"] = "video";
                              }
                              else{
                                $choice[0]["type_choice"] = "picture";
                              }
                            }
                            else{
                              $choice[0]["image"] = '';
                              $choice[0]["text"] = '';
                              $choice[0]["type_choice"] = "";
                            }
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
              
                          $choice[0]["bg_color"] = $Row1["choice_1_bg"];
                          $choice[0]["txt_color"] = $Row1["choice_1_txt_color"];
              
                          $choice[0]["ismyLike"] = false;
                          foreach($myLike as $Islike){
                            if($Islike == "0"){
                              $choice[0]["ismyLike"] = true;
                            }
                          }
              
                          $choice[0]["ismyVote"] = false;
              
                          if($RowChoiceVote['selector'] == '0'){
                            $choice[0]["ismyVote"] = true;
                          }
              
                          if($Row1["choice_2"] != '' || $Row1['choice_2_textImg'] != 0){
                            $exArr2 =  explode(".",$Row1["choice_2"]);
              
                            if($checkTxt2){
                              $choice[1]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[1];
                              $choice[1]["text"] = '';
                              $choice[1]["type_choice"] = "text";
                            }
                            else{
                              if($exArr2[1]){
                                $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                                $choice[1]["text"] = '';
                                if($exArr2[1] == "mp4"){
                                  $choice[1]["type_choice"] = "video";
                                }
                                else{
                                  $choice[1]["type_choice"] = "picture";
                                }
                              }
                              else{
                                $choice[1]["text"] = '';
                                $choice[1]["image"] = '';
                                $choice[1]["type_choice"] = "";
                              }
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
              
                            $choice[1]["bg_color"] = $Row1["choice_2_bg"];
                            $choice[1]["txt_color"] = $Row1["choice_2_txt_color"];
              
                            $choice[1]["ismyLike"] = false;
                            foreach($myLike as $Islike){
                              if($Islike == "1"){
                                $choice[1]["ismyLike"] = true;
                              }
                            }
              
                            $choice[1]["ismyVote"] = false;
              
                            if($RowChoiceVote['selector'] == '1'){
                              $choice[1]["ismyVote"] = true;
                            }
                          }
              
                          if($Row1["choice_3"] != '' || $Row1['choice_3_textImg'] != 0){
                            $choice[2]["ismyVote"] = false;
              
                            $exArr3 =  explode(".",$Row1["choice_3"]);
              
                            if($checkTxt3){
                              $choice[2]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[2];
                              $choice[2]["text"] = '';
                              $choice[2]["type_choice"] = "text";
                            }
                            else{
                              if($exArr3[1]){
                                $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                                $choice[2]["text"] = '';
                                if($exArr3[1] == "mp4"){
                                  $choice[2]["type_choice"] = "video";
                                }
                                else{
                                  $choice[2]["type_choice"] = "picture";
                                }
                              }
                              else{
                                $choice[2]["text"] = $Row1["choice_3"];
                                $choice[2]["image"] = '';
                                $choice[2]["type_choice"] = "text";
                              }
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
              
                            $choice[2]["bg_color"] = $Row1["choice_3_bg"];
                            $choice[2]["txt_color"] = $Row1["choice_3_txt_color"];
              
                            $choice[2]["ismyLike"] = false;
                            foreach($myLike as $Islike){
                              if($Islike == "2"){
                                $choice[2]["ismyLike"] = true;
                              }
                            }
              
                            if($RowChoiceVote['selector'] == '2'){
                              $choice[2]["ismyVote"] = true;
                            }
                          }
                
                          $arrRow["date_start"] = $Row1["date_start"];
                          $arrRow["time_start"] = $Row1["time_start"];
                          $arrRow["date_end"] = $Row1["date_end"];
                          $arrRow["time_end"] = $Row1["time_end"];
                          $arrRow["answer"] = intval($Row1["anwser"]);
                
                          // if($Row1["anwser"] == 1){
                          //   $arrRow["answer"] = 0;
                          // }
                          // elseif($Row1["anwser"] == 2){
                          //   $arrRow["answer"] = 1;
                          // }
                          // elseif($Row1["anwser"] == 3){
                          //   $arrRow["answer"] = 2;
                          // }
                
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
                
                      if($RowPic['choice_txtImg'] != 0){
                        $sqltxt_Img = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                        $sqltxt_Img->bind_param("i",$Row["id"]);
                        $sqltxt_Img->execute();
                        $querytxt_Img = $sqltxt_Img->get_result();
                
                        while($RowTxt_Img = $querytxt_Img->fetch_assoc()){
                          $TxtImg[] = $RowTxt_Img['picture'];
                        }
                
                        $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                        $choice[0]["text"] = "";
                        $choice[0]["type_choice"] = "text"; 
                      }
                      else{
                        $exArr1 =  explode(".",$RowPic["picture"]);
                        if($exArr1[1]){
                          $choice[0]["image"] = SITE_URL."uploads/posts/images/".$RowPic["picture"];
                          $choice[0]["text"] = "";
                          if($exArr1[1] == "mp4"){
                            $choice[0]["type_choice"] = "video"; 
                          }
                          else{
                            $choice[0]["type_choice"] = "picture"; 
                          }
                          
                        }
                        else{
                          $choice[0]["image"] = "";
                          $choice[0]["text"] = $RowPic["picture"];
                          $choice[0]["type_choice"] = "text"; 
                        }
                      }
                
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
                      $arrRow["date_start"] = "";
                      $arrRow["time_start"] = "";
                      $arrRow["date_end"] = "";
                      $arrRow["time_end"] = "";
                    }
                
                    //////////////////////////////////////////////////////////////////////////////////////////////
                
                    if($Row['staff_id'] > 0){
                      $sqluser = $mysqli->prepare("SELECT * FROM staff WHERE id = ? LIMIT 1");
                      $sqluser->bind_param("i",$Row["staff_id"]);
                      $sqluser->execute();
                      $queryUser = $sqluser->get_result();
                
                      if($queryUser->num_rows>0){
                        $RowUser = $queryUser->fetch_assoc();
                        $arrDataUser = array();
                
                        $arrDataUser["id"] = $RowUser["id"];
                        $arrDataUser["facebook_id"] = "";
                        $arrDataUser["gmail_id"] = "";
                        $arrDataUser["apple_id"] = "";
                        $arrDataUser["access_token"] = $RowUser["access_token"];
                        $arrDataUser["avatar"] = SITE_URL."uploads/staff/images/".$RowUser["avatar"];
                        $arrDataUser["cover"] = "";
                        $arrDataUser["username"] = $RowUser["username"];
                        $arrDataUser["first_name"] = $RowUser["fullname"];
                        $arrDataUser["last_name"] = "";
                        $arrDataUser["gender"] = "";
                        $arrDataUser["gender_other"] = "";
                        $arrDataUser["birthdate"] = "";
                        $arrDataUser["telephone"] = $RowUser["telephone"];
                        $arrDataUser["email"] = $RowUser["email"];
                        $arrDataUser["facebook"] = "";
                        $arrDataUser["line"] = "";
                        $arrDataUser["address"] = $RowUser["address"];
                        $arrDataUser["country_id"] = "";
                        $arrDataUser["country_other"] = "";
                        $arrDataUser["province_id"] = $RowUser["province_id"];
                        $arrDataUser["province_other"] = "";
                        $arrDataUser["occupation_id"] = "";
                        $arrDataUser["work_address"] = "";
                        $arrDataUser["about_me"] = "";
                        $arrDataUser["status"] = $RowUser["status"];
                        $arrDataUser["createdate"] = $RowUser["createdate"];
                        $arrDataUser["create_ip"] = $RowUser["create_ip"];
                        $arrDataUser["updatedate"] = $RowUser["updatedate"];
                        $arrDataUser["update_ip"] = $RowUser["update_ip"];
                        $arrDataUser["lastlogin"] = $RowUser["lastlogin"];
                        $arrDataUser["lastlogin_ip"] = $RowUser["lastlogin_ip"];
                
                        $arrDataUser["member_title"] = "";
                        $arrDataUser['member_coin'] = 0;
                        $arrDataUser['member_icon'] = "";
                
                        $arrRow["publisher"] = $arrDataUser;
                      }
                      else{
                        $arrRow["publisher"] = null;
                      }
                    }
                    else{
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
                
                        $sqlclass_user = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON a.class_name = b.title_en WHERE user_id = ? ");
                        $sqlclass_user->bind_param("i",$Row["user_id"]);
                        $sqlclass_user->execute();
                        $queryClass_User = $sqlclass_user->get_result();
                
                        if($queryClass_User->num_rows>0){
                          $RowClass_User = $queryClass_User->fetch_assoc();
                
                          switch($ac_lang){
                            case "en":
                              $arrDataUser["member_title"] = $RowClass_User["title_en"];
                              break;
                            case "th":
                              $arrDataUser["member_title"] = $RowClass_User["title_th"];
                              break;
                    
                            default:
                              $arrDataUser["member_title"] = $RowClass_User["title_th"];
                          }
                          $arrDataUser["member_coin"] = $RowClass_User["point"];
                          $arrDataUser["member_icon"] = SITE_URL."uploads/user_level/images/".$RowClass_User["picture"];
                        }
                        else{
                          $arrDataUser["member_title"] = "";
                          $arrDataUser['member_coin'] = 0;
                          $arrDataUser['member_icon'] = "";
                        }
              
                        $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
                        $sql_publisher_following->bind_param("i",$RowUser["id"]);
                        $sql_publisher_following->execute();
                        $query_publisher_following = $sql_publisher_following->get_result();
                        $Row_publisher_following = $query_publisher_following->fetch_assoc();
              
                        $arrDataUser['following_user'] = $Row_publisher_following['id'];
              
                        //////////////////////////////////////////////////////////////////////////////////
              
                        $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
                        $sql_publisher_follower->bind_param("i",$RowUser["id"]);
                        $sql_publisher_follower->execute();
                        $query_publisher_follower = $sql_publisher_follower->get_result();
                        $Row_publisher_follower = $query_publisher_follower->fetch_assoc();
              
                        $arrDataUser['follower_user'] = $Row_publisher_follower['id'];
              
                        ///////////////////////////////////////////////////////////////////////////////////
              
                        $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                        $sql_publisher_friend->bind_param("i",$RowUser["id"]);
                        $sql_publisher_friend->execute();
                        $query_publisher_friend = $sql_publisher_friend->get_result();
                        $Row_publisher_friend = $query_publisher_friend->fetch_assoc();
              
                        $arrDataUser['friend_user'] = $Row_publisher_friend['id'];
              
                        ///////////////////////////////////////////////////////////////////////////////////
              
                        $sql_publisher_friend_mutual = $mysqli->prepare("SELECT COUNT(id) FROM friend WHERE user_id IN (SELECT friend_id FROM `friend` WHERE user_id IN (SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id <> ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                        $sql_publisher_friend_mutual->bind_param("iii",$RowUser["id"],$RowUser["id"],$RowUser["id"]);
                        $sql_publisher_friend_mutual->execute();
                        $query_publisher_friend_mutual = $sql_publisher_friend_mutual->get_result();
                        $Row_publisher_friend_mutual = $query_publisher_friend_mutual->fetch_assoc();
              
                        $arrDataUser['friend_mutual_user'] = $Row_publisher_friend_mutual['COUNT(id)'];
              
                        ///////////////////////////////////////////////////////////////////////////////////
                
                        $arrRow["publisher"] = $arrDataUser;
                      }
                      else{
                        $arrRow["publisher"] = null;
                      }
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
                        switch($ac_lang){
                          case "en":
                            $ArrTopic["name"] = $RowInterest["name_en"];
                            break;
                
                          case "th":
                            $ArrTopic["name"] = $RowInterest["name_th"];
                            break;
                
                          default:
                            $ArrTopic["name"] = $RowInterest["name_th"];
                        }
                
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
                        $array_userTag["facebook_id"] = $RowUserTag["facebook_id"];
                        $array_userTag["gmail_id"] = $RowUserTag["gmail_id"];
                        $array_userTag["apple_id"] = $RowUserTag["apple_id"];
                        $array_userTag["access_token"] = $RowUserTag["access_token"];
                        $array_userTag["avatar"] = $RowUserTag["avatar"];
                        $array_userTag["cover"] = $RowUserTag["cover"];
                        $array_userTag["username"] = $RowUserTag["username"];
                        $array_userTag["first_name"] = $RowUserTag["first_name"];
                        $array_userTag["last_name"] = $RowUserTag["last_name"];
                        $array_userTag["gender"] = $RowUserTag["gender"];
                        $array_userTag["gender_other"] = $RowUserTag["gender_other"];
                        $array_userTag["birthdate"] = $RowUserTag["birthdate"];
                        $array_userTag["telephone"] = $RowUserTag["telephone"];
                        $array_userTag["email"] = $RowUserTag["email"];
                        $array_userTag["facebook"] = $RowUserTag["facebook"];
                        $array_userTag["line"] = $RowUserTag["line"];
                        $array_userTag["address"] = $RowUserTag["address"];
                        $array_userTag["country_id"] = $RowUserTag["country_id"];
                        $array_userTag["country_other"] = $RowUserTag["country_other"];
                        $array_userTag["province_id"] = $RowUserTag["province_id"];
                        $array_userTag["province_other"] = $RowUserTag["province_other"];
                        $array_userTag["occupation_id"] = $RowUserTag["occupation_id"];
                        $array_userTag["work_address"] = $RowUserTag["work_address"];
                        $array_userTag["about_me"] = $RowUserTag["about_me"];
                        $array_userTag["status"] = $RowUserTag["status"];
                        $array_userTag["createdate"] = $RowUserTag["createdate"];
                        $array_userTag["create_ip"] = $RowUserTag["create_ip"];
                        $array_userTag["updatedate"] = $RowUserTag["updatedate"];
                        $array_userTag["update_ip"] = $RowUserTag["update_ip"];
                        $array_userTag["lastlogin"] = $RowUserTag["lastlogin"];
                        $array_userTag["lastlogin_ip"] = $RowUserTag["lastlogin_ip"];
                
                        $sqlclass_user1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON a.class_name = b.title_en WHERE user_id = ? ");
                        $sqlclass_user1->bind_param("i",$Row["user_id"]);
                        $sqlclass_user1->execute();
                        $queryClass_User1 = $sqlclass_user1->get_result();
                
                        if($queryClass_User1->num_rows>0){
                          $RowClass_User1 = $queryClass_User1->fetch_assoc();
                
                          switch($ac_lang){
                            case "en":
                              $array_userTag["member_title"] = $RowClass_User1["title_en"];
                              break;
                            case "th":
                              $array_userTag["member_title"] = $RowClass_User1["title_th"];
                              break;
                    
                            default:
                              $array_userTag["member_title"] = $RowClass_User1["title_th"];
                          }
                          $array_userTag["member_coin"] = $RowClass_User1["point"];
                          $array_userTag["member_icon"] = SITE_URL."uploads/user_level/images/".$RowClass_User1["picture"];
                        }
                        else{
                          $array_userTag["member_title"] = "";
                          $array_userTag['member_coin'] = 0;
                          $array_userTag['member_icon'] = "";
                        }
              
                        $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
                        $sql_publisher_following->bind_param("i",$RowUser["id"]);
                        $sql_publisher_following->execute();
                        $query_publisher_following = $sql_publisher_following->get_result();
                        $Row_publisher_following = $query_publisher_following->fetch_assoc();
              
                        $arrDataUser['following_user'] = $Row_publisher_following['id'];
              
                        //////////////////////////////////////////////////////////////////////////////////
              
                        $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
                        $sql_publisher_follower->bind_param("i",$RowUser["id"]);
                        $sql_publisher_follower->execute();
                        $query_publisher_follower = $sql_publisher_follower->get_result();
                        $Row_publisher_follower = $query_publisher_follower->fetch_assoc();
              
                        $arrDataUser['follower_user'] = $Row_publisher_follower['id'];
              
                        ///////////////////////////////////////////////////////////////////////////////////
              
                        $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                        $sql_publisher_friend->bind_param("i",$RowUser["id"]);
                        $sql_publisher_friend->execute();
                        $query_publisher_friend = $sql_publisher_friend->get_result();
                        $Row_publisher_friend = $query_publisher_friend->fetch_assoc();
              
                        $arrDataUser['friend_user'] = $Row_publisher_friend['id'];
              
                        ///////////////////////////////////////////////////////////////////////////////////
                
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
                  }
                }
                else{
                  $Result['data'] = null;
                }

                $Result['data'] = $arrRow;

              }
              else{
                $Result['message'] = get_responseLang($ac_lang)[19];
              }
            }
            else{
              $Row2 = $query2->fetch_assoc();

              if($choice == '0'){
                $c_val = $Row2["choice1_like"] + 1;
                $sql4 = $mysqli->prepare("UPDATE post_choice_meta SET choice1_like = ? WHERE post_id = ?");
                $sql4->bind_param("ii",$c_val,$post_id);
                $sql4->execute();
              }
              elseif($choice == '1'){
                $c_val = $Row2["choice2_like"] + 1;
                $sql4 = $mysqli->prepare("UPDATE post_choice_meta SET choice2_like = ? WHERE post_id = ?");
                $sql4->bind_param("ii",$c_val,$post_id);
                $sql4->execute();
              }
              elseif($choice == '2'){
                $c_val = $Row2["choice3_like"] + 1;
                $sql4 = $mysqli->prepare("UPDATE post_choice_meta SET choice3_like = ? WHERE post_id = ?");
                $sql4->bind_param("ii",$c_val,$post_id);
                $sql4->execute();
              }

              //////////////////////////////////////////////////////////////////////////////////////////////////////////

              $Result['status'] = 'Success';
              $Result['message'] = get_responseLang($ac_lang)[4];

              $sqlF1 = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
              $sqlF1->bind_param("i",$post_id);
              $sqlF1->execute();
              $queryF1  =   $sqlF1->get_result();

              if($queryF1->num_rows>0){
                while($Row = $queryF1->fetch_assoc()){
                  $arrRow["id"] = $Row["id"];
                  $arrRow["title"] = $Row["title"];
                  $arrRow["post_type"] = $Row["type_vote"];
                  $arrRow["location"] = $Row["pin_map"];
                  $arrRow["post_style"] = $Row["type_create_post"];
                  $arrRow["post_status"] = $Row["post_status"];
                  $arrRow["vote_limit"] = $Row["limit_number_vote"];
              
                  $sqllike = $mysqli->prepare("SELECT user_like, user_comment, user_share FROM post_comment WHERE post_id = ? AND Status <> 'Delete' ");
                  $sqllike->bind_param("i",$Row["id"]);
                  $sqllike->execute();
                  $queryLike = $sqllike->get_result();
              
                  $countLike = 0;
                  $countComment = 0;
                  $countShare = 0;
              
                  ///////////////////////////////////////////////////////////////////////////////////////////////
              
                  $sql_post_like = $mysqli->prepare("SELECT * FROM post_comment WHERE post_id = ? AND user_like = ? AND Status <> 'Delete' ");
                  $sql_post_like->bind_param("ii",$Row["id"],$myid);
                  $sql_post_like->execute();
                  $query_post_Like = $sql_post_like->get_result();
              
                  if($query_post_Like->num_rows > 0){
                    $arrRow["post_ismyLike"] = true;
                  }
                  else{
                    $arrRow["post_ismyLike"] = false;
                  }
              
                  ///////////////////////////////////////////////////////////////////////////////////////////////
              
                  $sql_post_share = $mysqli->prepare("SELECT * FROM post_share WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
                  $sql_post_share->bind_param("ii",$Row["id"],$myid);
                  $sql_post_share->execute();
                  $query_post_share = $sql_post_share->get_result();
              
                  if($query_post_share->num_rows > 0){
                    $Rowshare = $query_post_share->fetch_assoc();
              
                    $arrRow["post_myshare"] = true;
                    $arrRow["type_share"] = $Rowshare['type_share'];
                  }
                  else{
                    $arrRow["post_myshare"] = false;
                    $arrRow["type_share"] = "";
                  }
              
                  ///////////////////////////////////////////////////////////////////////////////////////////////
              
                  while($RowLike = $queryLike->fetch_assoc()){
                    if($RowLike["user_like"] > 0){
                      $countLike += 1;
                    }
                    if($RowLike["user_comment"] > 0){
                      $countComment += 1;
                    }
                    if($RowLike["user_share"] > 0){
                      $countShare += 1;
                    }
                  }
              
                  $arrRow["likes"] = $countLike;
                  $arrRow["comments"] = $countComment;
                  $arrRow["shares"] = $countShare;
              
                  /////////////////////////////////////////////////////////////////////////////////////////////////
              
                  $sql_pin = $mysqli->prepare("SELECT * FROM post_pin WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
                  $sql_pin->bind_param("ii",$Row["id"],$myid);
                  $sql_pin->execute();
                  $query_post_pin = $sql_pin->get_result();
              
                  if($query_post_pin->num_rows > 0){
                    $arrRow["pin"] = true;
                  }
                  else{
                    $arrRow["pin"] = false;
                  }
              
                  ///////////////////////////////////////////////////////////////////////////////////////////////
              
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
                        while($RowChoice = $queryChoice->fetch_assoc()){
                          $myLike[] = $RowChoice['choice_like'];
                        }
                        
                        $choice = array();  
              
                        //////////////////////////////////////////////////////////////////////////
              
                        $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                        $sqlchoice_vote->bind_param("ii",$Row["id"],$myid);
                        $sqlchoice_vote->execute();
                        $queryChoiceVote = $sqlchoice_vote->get_result();
                        $RowChoiceVote = $queryChoiceVote->fetch_assoc();
                        
                        ////////////////////////////////////////////////////////////////////////// 
            
                        $checkTxt1 = false;
                        $checkTxt2 = false;
                        $checkTxt3 = false;
            
                        $sqltxt_Img1 = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                        $sqltxt_Img1->bind_param("i",$Row["id"]);
                        $sqltxt_Img1->execute();
                        $querytxt_Img1 = $sqltxt_Img1->get_result();
            
                        while($RowTxt_Img = $querytxt_Img1->fetch_assoc()){
                          $TxtImg[] = $RowTxt_Img['picture'];
                        }
              
                        if($Row1['choice_1_textImg'] != 0){
                          $checkTxt1 = true;
                        }
                        if($Row1['choice_2_textImg'] != 0){
                          $checkTxt2 = true;
                        }
                        if($Row1['choice_3_textImg'] != 0){
                          $checkTxt3 = true;
                        }
            
                        $exArr1 =  explode(".",$Row1["choice_1"]);
            
                        if($checkTxt1){
                          $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                          $choice[0]["text"] = '';
                          $choice[0]["type_choice"] = "text";
                        }
                        else{
                          if($exArr1[1]){
                            $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                            $choice[0]["text"] = '';
                            if($exArr1[1] == "mp4"){
                              $choice[0]["type_choice"] = "video";
                            }
                            else{
                              $choice[0]["type_choice"] = "picture";
                            }
                          }
                          else{
                            $choice[0]["image"] = '';
                            $choice[0]["text"] = '';
                            $choice[0]["type_choice"] = "";
                          }
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
            
                        $choice[0]["bg_color"] = $Row1["choice_1_bg"];
                        $choice[0]["txt_color"] = $Row1["choice_1_txt_color"];
            
                        $choice[0]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == "0"){
                            $choice[0]["ismyLike"] = true;
                          }
                        }
            
                        $choice[0]["ismyVote"] = false;
            
                        if($RowChoiceVote['selector'] == '0'){
                          $choice[0]["ismyVote"] = true;
                        }
            
                        if($Row1["choice_2"] != '' || $Row1['choice_2_textImg'] != 0){
                          $exArr2 =  explode(".",$Row1["choice_2"]);
            
                          if($checkTxt2){
                            $choice[1]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[1];
                            $choice[1]["text"] = '';
                            $choice[1]["type_choice"] = "text";
                          }
                          else{
                            if($exArr2[1]){
                              $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                              $choice[1]["text"] = '';
                              if($exArr2[1] == "mp4"){
                                $choice[1]["type_choice"] = "video";
                              }
                              else{
                                $choice[1]["type_choice"] = "picture";
                              }
                            }
                            else{
                              $choice[1]["text"] = '';
                              $choice[1]["image"] = '';
                              $choice[1]["type_choice"] = "";
                            }
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
            
                          $choice[1]["bg_color"] = $Row1["choice_2_bg"];
                          $choice[1]["txt_color"] = $Row1["choice_2_txt_color"];
            
                          $choice[1]["ismyLike"] = false;
                          foreach($myLike as $Islike){
                            if($Islike == "1"){
                              $choice[1]["ismyLike"] = true;
                            }
                          }
            
                          $choice[1]["ismyVote"] = false;
            
                          if($RowChoiceVote['selector'] == '1'){
                            $choice[1]["ismyVote"] = true;
                          }
                        }
            
                        if($Row1["choice_3"] != '' || $Row1['choice_3_textImg'] != 0){
                          $choice[2]["ismyVote"] = false;
            
                          $exArr3 =  explode(".",$Row1["choice_3"]);
            
                          if($checkTxt3){
                            $choice[2]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[2];
                            $choice[2]["text"] = '';
                            $choice[2]["type_choice"] = "text";
                          }
                          else{
                            if($exArr3[1]){
                              $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                              $choice[2]["text"] = '';
                              if($exArr3[1] == "mp4"){
                                $choice[2]["type_choice"] = "video";
                              }
                              else{
                                $choice[2]["type_choice"] = "picture";
                              }
                            }
                            else{
                              $choice[2]["text"] = $Row1["choice_3"];
                              $choice[2]["image"] = '';
                              $choice[2]["type_choice"] = "text";
                            }
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
            
                          $choice[2]["bg_color"] = $Row1["choice_3_bg"];
                          $choice[2]["txt_color"] = $Row1["choice_3_txt_color"];
            
                          $choice[2]["ismyLike"] = false;
                          foreach($myLike as $Islike){
                            if($Islike == "2"){
                              $choice[2]["ismyLike"] = true;
                            }
                          }
            
                          if($RowChoiceVote['selector'] == '2'){
                            $choice[2]["ismyVote"] = true;
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
              
                        //////////////////////////////////////////////////////////////////////////
              
                        $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                        $sqlchoice_vote->bind_param("ii",$Row["id"],$myid);
                        $sqlchoice_vote->execute();
                        $queryChoiceVote = $sqlchoice_vote->get_result();
                        
                        while($RowChoiceVote = $queryChoiceVote->fetch_assoc()){
                          $myLike[] = $RowChoiceVote['choice_like'];
                        }
              
                        //////////////////////////////////////////////////////////////////////////
              
                        $checkTxt1 = false;
                        $checkTxt2 = false;
                        $checkTxt3 = false;
            
                        $sqltxt_Img1 = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                        $sqltxt_Img1->bind_param("i",$Row["id"]);
                        $sqltxt_Img1->execute();
                        $querytxt_Img1 = $sqltxt_Img1->get_result();
            
                        while($RowTxt_Img = $querytxt_Img1->fetch_assoc()){
                          $TxtImg[] = $RowTxt_Img['picture'];
                        }
              
                        if($Row1['choice_1_textImg'] != 0){
                          $checkTxt1 = true;
                        }
                        if($Row1['choice_2_textImg'] != 0){
                          $checkTxt2 = true;
                        }
                        if($Row1['choice_3_textImg'] != 0){
                          $checkTxt3 = true;
                        }
            
                        $exArr1 =  explode(".",$Row1["choice_1"]);
            
                        if($checkTxt1){
                          $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                          $choice[0]["text"] = '';
                          $choice[0]["type_choice"] = "text";
                        }
                        else{
                          if($exArr1[1]){
                            $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                            $choice[0]["text"] = '';
                            if($exArr1[1] == "mp4"){
                              $choice[0]["type_choice"] = "video";
                            }
                            else{
                              $choice[0]["type_choice"] = "picture";
                            }
                          }
                          else{
                            $choice[0]["image"] = '';
                            $choice[0]["text"] = '';
                            $choice[0]["type_choice"] = "";
                          }
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
            
                        $choice[0]["bg_color"] = $Row1["choice_1_bg"];
                        $choice[0]["txt_color"] = $Row1["choice_1_txt_color"];
            
                        $choice[0]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == "0"){
                            $choice[0]["ismyLike"] = true;
                          }
                        }
            
                        $choice[0]["ismyVote"] = false;
            
                        if($RowChoiceVote['selector'] == '0'){
                          $choice[0]["ismyVote"] = true;
                        }
            
                        if($Row1["choice_2"] != '' || $Row1['choice_2_textImg'] != 0){
                          $exArr2 =  explode(".",$Row1["choice_2"]);
            
                          if($checkTxt2){
                            $choice[1]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[1];
                            $choice[1]["text"] = '';
                            $choice[1]["type_choice"] = "text";
                          }
                          else{
                            if($exArr2[1]){
                              $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                              $choice[1]["text"] = '';
                              if($exArr2[1] == "mp4"){
                                $choice[1]["type_choice"] = "video";
                              }
                              else{
                                $choice[1]["type_choice"] = "picture";
                              }
                            }
                            else{
                              $choice[1]["text"] = '';
                              $choice[1]["image"] = '';
                              $choice[1]["type_choice"] = "";
                            }
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
            
                          $choice[1]["bg_color"] = $Row1["choice_2_bg"];
                          $choice[1]["txt_color"] = $Row1["choice_2_txt_color"];
            
                          $choice[1]["ismyLike"] = false;
                          foreach($myLike as $Islike){
                            if($Islike == "1"){
                              $choice[1]["ismyLike"] = true;
                            }
                          }
            
                          $choice[1]["ismyVote"] = false;
            
                          if($RowChoiceVote['selector'] == '1'){
                            $choice[1]["ismyVote"] = true;
                          }
                        }
            
                        if($Row1["choice_3"] != '' || $Row1['choice_3_textImg'] != 0){
                          $choice[2]["ismyVote"] = false;
            
                          $exArr3 =  explode(".",$Row1["choice_3"]);
            
                          if($checkTxt3){
                            $choice[2]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[2];
                            $choice[2]["text"] = '';
                            $choice[2]["type_choice"] = "text";
                          }
                          else{
                            if($exArr3[1]){
                              $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                              $choice[2]["text"] = '';
                              if($exArr3[1] == "mp4"){
                                $choice[2]["type_choice"] = "video";
                              }
                              else{
                                $choice[2]["type_choice"] = "picture";
                              }
                            }
                            else{
                              $choice[2]["text"] = $Row1["choice_3"];
                              $choice[2]["image"] = '';
                              $choice[2]["type_choice"] = "text";
                            }
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
            
                          $choice[2]["bg_color"] = $Row1["choice_3_bg"];
                          $choice[2]["txt_color"] = $Row1["choice_3_txt_color"];
            
                          $choice[2]["ismyLike"] = false;
                          foreach($myLike as $Islike){
                            if($Islike == "2"){
                              $choice[2]["ismyLike"] = true;
                            }
                          }
            
                          if($RowChoiceVote['selector'] == '2'){
                            $choice[2]["ismyVote"] = true;
                          }
                        }
              
                        $arrRow["date_start"] = $Row1["date_start"];
                        $arrRow["time_start"] = $Row1["time_start"];
                        $arrRow["date_end"] = $Row1["date_end"];
                        $arrRow["time_end"] = $Row1["time_end"];
                        $arrRow["answer"] = intval($Row1["anwser"]);
              
                        // if($Row1["anwser"] == 1){
                        //   $arrRow["answer"] = 0;
                        // }
                        // elseif($Row1["anwser"] == 2){
                        //   $arrRow["answer"] = 1;
                        // }
                        // elseif($Row1["anwser"] == 3){
                        //   $arrRow["answer"] = 2;
                        // }
              
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
              
                    if($RowPic['choice_txtImg'] != 0){
                      $sqltxt_Img = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                      $sqltxt_Img->bind_param("i",$Row["id"]);
                      $sqltxt_Img->execute();
                      $querytxt_Img = $sqltxt_Img->get_result();
              
                      while($RowTxt_Img = $querytxt_Img->fetch_assoc()){
                        $TxtImg[] = $RowTxt_Img['picture'];
                      }
              
                      $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                      $choice[0]["text"] = "";
                      $choice[0]["type_choice"] = "text"; 
                    }
                    else{
                      $exArr1 =  explode(".",$RowPic["picture"]);
                      if($exArr1[1]){
                        $choice[0]["image"] = SITE_URL."uploads/posts/images/".$RowPic["picture"];
                        $choice[0]["text"] = "";
                        if($exArr1[1] == "mp4"){
                          $choice[0]["type_choice"] = "video"; 
                        }
                        else{
                          $choice[0]["type_choice"] = "picture"; 
                        }
                        
                      }
                      else{
                        $choice[0]["image"] = "";
                        $choice[0]["text"] = $RowPic["picture"];
                        $choice[0]["type_choice"] = "text"; 
                      }
                    }
              
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
                    $arrRow["date_start"] = "";
                    $arrRow["time_start"] = "";
                    $arrRow["date_end"] = "";
                    $arrRow["time_end"] = "";
                  }
              
                  //////////////////////////////////////////////////////////////////////////////////////////////
              
                  if($Row['staff_id'] > 0){
                    $sqluser = $mysqli->prepare("SELECT * FROM staff WHERE id = ? LIMIT 1");
                    $sqluser->bind_param("i",$Row["staff_id"]);
                    $sqluser->execute();
                    $queryUser = $sqluser->get_result();
              
                    if($queryUser->num_rows>0){
                      $RowUser = $queryUser->fetch_assoc();
                      $arrDataUser = array();
              
                      $arrDataUser["id"] = $RowUser["id"];
                      $arrDataUser["facebook_id"] = "";
                      $arrDataUser["gmail_id"] = "";
                      $arrDataUser["apple_id"] = "";
                      $arrDataUser["access_token"] = $RowUser["access_token"];
                      $arrDataUser["avatar"] = SITE_URL."uploads/staff/images/".$RowUser["avatar"];
                      $arrDataUser["cover"] = "";
                      $arrDataUser["username"] = $RowUser["username"];
                      $arrDataUser["first_name"] = $RowUser["fullname"];
                      $arrDataUser["last_name"] = "";
                      $arrDataUser["gender"] = "";
                      $arrDataUser["gender_other"] = "";
                      $arrDataUser["birthdate"] = "";
                      $arrDataUser["telephone"] = $RowUser["telephone"];
                      $arrDataUser["email"] = $RowUser["email"];
                      $arrDataUser["facebook"] = "";
                      $arrDataUser["line"] = "";
                      $arrDataUser["address"] = $RowUser["address"];
                      $arrDataUser["country_id"] = "";
                      $arrDataUser["country_other"] = "";
                      $arrDataUser["province_id"] = $RowUser["province_id"];
                      $arrDataUser["province_other"] = "";
                      $arrDataUser["occupation_id"] = "";
                      $arrDataUser["work_address"] = "";
                      $arrDataUser["about_me"] = "";
                      $arrDataUser["status"] = $RowUser["status"];
                      $arrDataUser["createdate"] = $RowUser["createdate"];
                      $arrDataUser["create_ip"] = $RowUser["create_ip"];
                      $arrDataUser["updatedate"] = $RowUser["updatedate"];
                      $arrDataUser["update_ip"] = $RowUser["update_ip"];
                      $arrDataUser["lastlogin"] = $RowUser["lastlogin"];
                      $arrDataUser["lastlogin_ip"] = $RowUser["lastlogin_ip"];
              
                      $arrDataUser["member_title"] = "";
                      $arrDataUser['member_coin'] = 0;
                      $arrDataUser['member_icon'] = "";
              
                      $arrRow["publisher"] = $arrDataUser;
                    }
                    else{
                      $arrRow["publisher"] = null;
                    }
                  }
                  else{
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
              
                      $sqlclass_user = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON a.class_name = b.title_en WHERE user_id = ? ");
                      $sqlclass_user->bind_param("i",$Row["user_id"]);
                      $sqlclass_user->execute();
                      $queryClass_User = $sqlclass_user->get_result();
              
                      if($queryClass_User->num_rows>0){
                        $RowClass_User = $queryClass_User->fetch_assoc();
              
                        switch($ac_lang){
                          case "en":
                            $arrDataUser["member_title"] = $RowClass_User["title_en"];
                            break;
                          case "th":
                            $arrDataUser["member_title"] = $RowClass_User["title_th"];
                            break;
                  
                          default:
                            $arrDataUser["member_title"] = $RowClass_User["title_th"];
                        }
                        $arrDataUser["member_coin"] = $RowClass_User["point"];
                        $arrDataUser["member_icon"] = SITE_URL."uploads/user_level/images/".$RowClass_User["picture"];
                      }
                      else{
                        $arrDataUser["member_title"] = "";
                        $arrDataUser['member_coin'] = 0;
                        $arrDataUser['member_icon'] = "";
                      }
            
                      $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
                      $sql_publisher_following->bind_param("i",$RowUser["id"]);
                      $sql_publisher_following->execute();
                      $query_publisher_following = $sql_publisher_following->get_result();
                      $Row_publisher_following = $query_publisher_following->fetch_assoc();
            
                      $arrDataUser['following_user'] = $Row_publisher_following['id'];
            
                      //////////////////////////////////////////////////////////////////////////////////
            
                      $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
                      $sql_publisher_follower->bind_param("i",$RowUser["id"]);
                      $sql_publisher_follower->execute();
                      $query_publisher_follower = $sql_publisher_follower->get_result();
                      $Row_publisher_follower = $query_publisher_follower->fetch_assoc();
            
                      $arrDataUser['follower_user'] = $Row_publisher_follower['id'];
            
                      ///////////////////////////////////////////////////////////////////////////////////
            
                      $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                      $sql_publisher_friend->bind_param("i",$RowUser["id"]);
                      $sql_publisher_friend->execute();
                      $query_publisher_friend = $sql_publisher_friend->get_result();
                      $Row_publisher_friend = $query_publisher_friend->fetch_assoc();
            
                      $arrDataUser['friend_user'] = $Row_publisher_friend['id'];
            
                      ///////////////////////////////////////////////////////////////////////////////////
            
                      $sql_publisher_friend_mutual = $mysqli->prepare("SELECT COUNT(id) FROM friend WHERE user_id IN (SELECT friend_id FROM `friend` WHERE user_id IN (SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id <> ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                      $sql_publisher_friend_mutual->bind_param("iii",$RowUser["id"],$RowUser["id"],$RowUser["id"]);
                      $sql_publisher_friend_mutual->execute();
                      $query_publisher_friend_mutual = $sql_publisher_friend_mutual->get_result();
                      $Row_publisher_friend_mutual = $query_publisher_friend_mutual->fetch_assoc();
            
                      $arrDataUser['friend_mutual_user'] = $Row_publisher_friend_mutual['COUNT(id)'];
            
                      ///////////////////////////////////////////////////////////////////////////////////
              
                      $arrRow["publisher"] = $arrDataUser;
                    }
                    else{
                      $arrRow["publisher"] = null;
                    }
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
                      switch($ac_lang){
                        case "en":
                          $ArrTopic["name"] = $RowInterest["name_en"];
                          break;
              
                        case "th":
                          $ArrTopic["name"] = $RowInterest["name_th"];
                          break;
              
                        default:
                          $ArrTopic["name"] = $RowInterest["name_th"];
                      }
              
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
                      $array_userTag["facebook_id"] = $RowUserTag["facebook_id"];
                      $array_userTag["gmail_id"] = $RowUserTag["gmail_id"];
                      $array_userTag["apple_id"] = $RowUserTag["apple_id"];
                      $array_userTag["access_token"] = $RowUserTag["access_token"];
                      $array_userTag["avatar"] = $RowUserTag["avatar"];
                      $array_userTag["cover"] = $RowUserTag["cover"];
                      $array_userTag["username"] = $RowUserTag["username"];
                      $array_userTag["first_name"] = $RowUserTag["first_name"];
                      $array_userTag["last_name"] = $RowUserTag["last_name"];
                      $array_userTag["gender"] = $RowUserTag["gender"];
                      $array_userTag["gender_other"] = $RowUserTag["gender_other"];
                      $array_userTag["birthdate"] = $RowUserTag["birthdate"];
                      $array_userTag["telephone"] = $RowUserTag["telephone"];
                      $array_userTag["email"] = $RowUserTag["email"];
                      $array_userTag["facebook"] = $RowUserTag["facebook"];
                      $array_userTag["line"] = $RowUserTag["line"];
                      $array_userTag["address"] = $RowUserTag["address"];
                      $array_userTag["country_id"] = $RowUserTag["country_id"];
                      $array_userTag["country_other"] = $RowUserTag["country_other"];
                      $array_userTag["province_id"] = $RowUserTag["province_id"];
                      $array_userTag["province_other"] = $RowUserTag["province_other"];
                      $array_userTag["occupation_id"] = $RowUserTag["occupation_id"];
                      $array_userTag["work_address"] = $RowUserTag["work_address"];
                      $array_userTag["about_me"] = $RowUserTag["about_me"];
                      $array_userTag["status"] = $RowUserTag["status"];
                      $array_userTag["createdate"] = $RowUserTag["createdate"];
                      $array_userTag["create_ip"] = $RowUserTag["create_ip"];
                      $array_userTag["updatedate"] = $RowUserTag["updatedate"];
                      $array_userTag["update_ip"] = $RowUserTag["update_ip"];
                      $array_userTag["lastlogin"] = $RowUserTag["lastlogin"];
                      $array_userTag["lastlogin_ip"] = $RowUserTag["lastlogin_ip"];
              
                      $sqlclass_user1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON a.class_name = b.title_en WHERE user_id = ? ");
                      $sqlclass_user1->bind_param("i",$Row["user_id"]);
                      $sqlclass_user1->execute();
                      $queryClass_User1 = $sqlclass_user1->get_result();
              
                      if($queryClass_User1->num_rows>0){
                        $RowClass_User1 = $queryClass_User1->fetch_assoc();
              
                        switch($ac_lang){
                          case "en":
                            $array_userTag["member_title"] = $RowClass_User1["title_en"];
                            break;
                          case "th":
                            $array_userTag["member_title"] = $RowClass_User1["title_th"];
                            break;
                  
                          default:
                            $array_userTag["member_title"] = $RowClass_User1["title_th"];
                        }
                        $array_userTag["member_coin"] = $RowClass_User1["point"];
                        $array_userTag["member_icon"] = SITE_URL."uploads/user_level/images/".$RowClass_User1["picture"];
                      }
                      else{
                        $array_userTag["member_title"] = "";
                        $array_userTag['member_coin'] = 0;
                        $array_userTag['member_icon'] = "";
                      }
            
                      $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
                      $sql_publisher_following->bind_param("i",$RowUser["id"]);
                      $sql_publisher_following->execute();
                      $query_publisher_following = $sql_publisher_following->get_result();
                      $Row_publisher_following = $query_publisher_following->fetch_assoc();
            
                      $arrDataUser['following_user'] = $Row_publisher_following['id'];
            
                      //////////////////////////////////////////////////////////////////////////////////
            
                      $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
                      $sql_publisher_follower->bind_param("i",$RowUser["id"]);
                      $sql_publisher_follower->execute();
                      $query_publisher_follower = $sql_publisher_follower->get_result();
                      $Row_publisher_follower = $query_publisher_follower->fetch_assoc();
            
                      $arrDataUser['follower_user'] = $Row_publisher_follower['id'];
            
                      ///////////////////////////////////////////////////////////////////////////////////
            
                      $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                      $sql_publisher_friend->bind_param("i",$RowUser["id"]);
                      $sql_publisher_friend->execute();
                      $query_publisher_friend = $sql_publisher_friend->get_result();
                      $Row_publisher_friend = $query_publisher_friend->fetch_assoc();
            
                      $arrDataUser['friend_user'] = $Row_publisher_friend['id'];
            
                      ///////////////////////////////////////////////////////////////////////////////////
              
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
                }

              }
              else{
                $Result['data'] = null;
              }

              $Result['data'] = $arrRow;
            }
          }
          else{
            $Result['message'] = get_responseLang($ac_lang)[19];
          }
        }
        else{
          $status = 'Delete';

          $sql = $mysqli->prepare("UPDATE post_choice_meta_user SET Status=? WHERE post_id=? AND user_id=? AND choice_like = ? ");
          $sql->bind_param("siii",$status,$post_id,$myid,$choice);
  
          if($sql->execute()){
            $presql3 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? AND Status <> 'Delete' ");
            $presql3->bind_param("i",$post_id,);
            $presql3->execute();
            $prequery3 = $presql3->get_result();
            $RowPre3 = $prequery3->fetch_assoc();

            if($choice == "0"){
              if($RowPre3["choice1_like"] > 0){
                $sum_choice = $RowPre3["choice1_like"] - 1;
              }
              else{
                $sum_choice = 0;
              }

              $sql2 = $mysqli->prepare("UPDATE post_choice_meta SET choice1_like = ? WHERE post_id = ? AND Status <> 'Delete' ");
              $sql2->bind_param("ii",$sum_choice,$post_id);
              $sql2->execute();
            }
            elseif($choice == "1"){
              if($RowPre3["choice2_like"] > 0){
                $sum_choice = $RowPre3["choice2_like"] - 1;
              }
              else{
                $sum_choice = 0;
              }

              $sql2 = $mysqli->prepare("UPDATE post_choice_meta SET choice2_like = ? WHERE post_id = ? AND Status <> 'Delete' ");
              $sql2->bind_param("ii",$sum_choice,$post_id);
              $sql2->execute();
            }
            elseif($choice == "2"){
              if($RowPre3["choice3_like"] > 0){
                $sum_choice = $RowPre3["choice3_like"] - 1;
              }
              else{
                $sum_choice = 0;
              }

              $sql2 = $mysqli->prepare("UPDATE post_choice_meta SET choice3_like = ? WHERE post_id = ? AND Status <> 'Delete' ");
              $sql2->bind_param("ii",$sum_choice,$post_id);
              $sql2->execute();
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////

            $Result['status'] = 'Success';
            $Result['message'] = get_responseLang($ac_lang)[5];

            $sqlF1 = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
            $sqlF1->bind_param("i",$post_id);
            $sqlF1->execute();
            $queryF1  =   $sqlF1->get_result();

            if($queryF1->num_rows>0){
              while($Row = $queryF1->fetch_assoc()){
                $arrRow["id"] = $Row["id"];
                $arrRow["title"] = $Row["title"];
                $arrRow["post_type"] = $Row["type_vote"];
                $arrRow["location"] = $Row["pin_map"];
                $arrRow["post_style"] = $Row["type_create_post"];
                $arrRow["post_status"] = $Row["post_status"];
                $arrRow["vote_limit"] = $Row["limit_number_vote"];
            
                $sqllike = $mysqli->prepare("SELECT user_like, user_comment, user_share FROM post_comment WHERE post_id = ? AND Status <> 'Delete' ");
                $sqllike->bind_param("i",$Row["id"]);
                $sqllike->execute();
                $queryLike = $sqllike->get_result();
            
                $countLike = 0;
                $countComment = 0;
                $countShare = 0;
            
                ///////////////////////////////////////////////////////////////////////////////////////////////
            
                $sql_post_like = $mysqli->prepare("SELECT * FROM post_comment WHERE post_id = ? AND user_like = ? AND Status <> 'Delete' ");
                $sql_post_like->bind_param("ii",$Row["id"],$myid);
                $sql_post_like->execute();
                $query_post_Like = $sql_post_like->get_result();
            
                if($query_post_Like->num_rows > 0){
                  $arrRow["post_ismyLike"] = true;
                }
                else{
                  $arrRow["post_ismyLike"] = false;
                }
            
                ///////////////////////////////////////////////////////////////////////////////////////////////
            
                $sql_post_share = $mysqli->prepare("SELECT * FROM post_share WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
                $sql_post_share->bind_param("ii",$Row["id"],$myid);
                $sql_post_share->execute();
                $query_post_share = $sql_post_share->get_result();
            
                if($query_post_share->num_rows > 0){
                  $Rowshare = $query_post_share->fetch_assoc();
            
                  $arrRow["post_myshare"] = true;
                  $arrRow["type_share"] = $Rowshare['type_share'];
                }
                else{
                  $arrRow["post_myshare"] = false;
                  $arrRow["type_share"] = "";
                }
            
                ///////////////////////////////////////////////////////////////////////////////////////////////
            
                while($RowLike = $queryLike->fetch_assoc()){
                  if($RowLike["user_like"] > 0){
                    $countLike += 1;
                  }
                  if($RowLike["user_comment"] > 0){
                    $countComment += 1;
                  }
                  if($RowLike["user_share"] > 0){
                    $countShare += 1;
                  }
                }
            
                $arrRow["likes"] = $countLike;
                $arrRow["comments"] = $countComment;
                $arrRow["shares"] = $countShare;
            
                /////////////////////////////////////////////////////////////////////////////////////////////////
            
                $sql_pin = $mysqli->prepare("SELECT * FROM post_pin WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' LIMIT 1");
                $sql_pin->bind_param("ii",$Row["id"],$myid);
                $sql_pin->execute();
                $query_post_pin = $sql_pin->get_result();
            
                if($query_post_pin->num_rows > 0){
                  $arrRow["pin"] = true;
                }
                else{
                  $arrRow["pin"] = false;
                }
            
                ///////////////////////////////////////////////////////////////////////////////////////////////
            
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
                      while($RowChoice = $queryChoice->fetch_assoc()){
                        $myLike[] = $RowChoice['choice_like'];
                      }
                      
                      $choice = array();  
            
                      //////////////////////////////////////////////////////////////////////////
            
                      $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                      $sqlchoice_vote->bind_param("ii",$Row["id"],$myid);
                      $sqlchoice_vote->execute();
                      $queryChoiceVote = $sqlchoice_vote->get_result();
                      $RowChoiceVote = $queryChoiceVote->fetch_assoc();
                      
                      ////////////////////////////////////////////////////////////////////////// 
          
                      $checkTxt1 = false;
                      $checkTxt2 = false;
                      $checkTxt3 = false;
          
                      $sqltxt_Img1 = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                      $sqltxt_Img1->bind_param("i",$Row["id"]);
                      $sqltxt_Img1->execute();
                      $querytxt_Img1 = $sqltxt_Img1->get_result();
          
                      while($RowTxt_Img = $querytxt_Img1->fetch_assoc()){
                        $TxtImg[] = $RowTxt_Img['picture'];
                      }
            
                      if($Row1['choice_1_textImg'] != 0){
                        $checkTxt1 = true;
                      }
                      if($Row1['choice_2_textImg'] != 0){
                        $checkTxt2 = true;
                      }
                      if($Row1['choice_3_textImg'] != 0){
                        $checkTxt3 = true;
                      }
          
                      $exArr1 =  explode(".",$Row1["choice_1"]);
          
                      if($checkTxt1){
                        $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                        $choice[0]["text"] = '';
                        $choice[0]["type_choice"] = "text";
                      }
                      else{
                        if($exArr1[1]){
                          $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                          $choice[0]["text"] = '';
                          if($exArr1[1] == "mp4"){
                            $choice[0]["type_choice"] = "video";
                          }
                          else{
                            $choice[0]["type_choice"] = "picture";
                          }
                        }
                        else{
                          $choice[0]["image"] = '';
                          $choice[0]["text"] = '';
                          $choice[0]["type_choice"] = "";
                        }
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
          
                      $choice[0]["bg_color"] = $Row1["choice_1_bg"];
                      $choice[0]["txt_color"] = $Row1["choice_1_txt_color"];
          
                      $choice[0]["ismyLike"] = false;
                      foreach($myLike as $Islike){
                        if($Islike == "0"){
                          $choice[0]["ismyLike"] = true;
                        }
                      }
          
                      $choice[0]["ismyVote"] = false;
          
                      if($RowChoiceVote['selector'] == '0'){
                        $choice[0]["ismyVote"] = true;
                      }
          
                      if($Row1["choice_2"] != '' || $Row1['choice_2_textImg'] != 0){
                        $exArr2 =  explode(".",$Row1["choice_2"]);
          
                        if($checkTxt2){
                          $choice[1]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[1];
                          $choice[1]["text"] = '';
                          $choice[1]["type_choice"] = "text";
                        }
                        else{
                          if($exArr2[1]){
                            $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                            $choice[1]["text"] = '';
                            if($exArr2[1] == "mp4"){
                              $choice[1]["type_choice"] = "video";
                            }
                            else{
                              $choice[1]["type_choice"] = "picture";
                            }
                          }
                          else{
                            $choice[1]["text"] = '';
                            $choice[1]["image"] = '';
                            $choice[1]["type_choice"] = "";
                          }
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
          
                        $choice[1]["bg_color"] = $Row1["choice_2_bg"];
                        $choice[1]["txt_color"] = $Row1["choice_2_txt_color"];
          
                        $choice[1]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == "1"){
                            $choice[1]["ismyLike"] = true;
                          }
                        }
          
                        $choice[1]["ismyVote"] = false;
          
                        if($RowChoiceVote['selector'] == '1'){
                          $choice[1]["ismyVote"] = true;
                        }
                      }
          
                      if($Row1["choice_3"] != '' || $Row1['choice_3_textImg'] != 0){
                        $choice[2]["ismyVote"] = false;
          
                        $exArr3 =  explode(".",$Row1["choice_3"]);
          
                        if($checkTxt3){
                          $choice[2]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[2];
                          $choice[2]["text"] = '';
                          $choice[2]["type_choice"] = "text";
                        }
                        else{
                          if($exArr3[1]){
                            $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                            $choice[2]["text"] = '';
                            if($exArr3[1] == "mp4"){
                              $choice[2]["type_choice"] = "video";
                            }
                            else{
                              $choice[2]["type_choice"] = "picture";
                            }
                          }
                          else{
                            $choice[2]["text"] = $Row1["choice_3"];
                            $choice[2]["image"] = '';
                            $choice[2]["type_choice"] = "text";
                          }
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
          
                        $choice[2]["bg_color"] = $Row1["choice_3_bg"];
                        $choice[2]["txt_color"] = $Row1["choice_3_txt_color"];
          
                        $choice[2]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == "2"){
                            $choice[2]["ismyLike"] = true;
                          }
                        }
          
                        if($RowChoiceVote['selector'] == '2'){
                          $choice[2]["ismyVote"] = true;
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
            
                      //////////////////////////////////////////////////////////////////////////
            
                      $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                      $sqlchoice_vote->bind_param("ii",$Row["id"],$myid);
                      $sqlchoice_vote->execute();
                      $queryChoiceVote = $sqlchoice_vote->get_result();
                      
                      while($RowChoiceVote = $queryChoiceVote->fetch_assoc()){
                        $myLike[] = $RowChoiceVote['choice_like'];
                      }
            
                      //////////////////////////////////////////////////////////////////////////
            
                      $checkTxt1 = false;
                      $checkTxt2 = false;
                      $checkTxt3 = false;
          
                      $sqltxt_Img1 = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                      $sqltxt_Img1->bind_param("i",$Row["id"]);
                      $sqltxt_Img1->execute();
                      $querytxt_Img1 = $sqltxt_Img1->get_result();
          
                      while($RowTxt_Img = $querytxt_Img1->fetch_assoc()){
                        $TxtImg[] = $RowTxt_Img['picture'];
                      }
            
                      if($Row1['choice_1_textImg'] != 0){
                        $checkTxt1 = true;
                      }
                      if($Row1['choice_2_textImg'] != 0){
                        $checkTxt2 = true;
                      }
                      if($Row1['choice_3_textImg'] != 0){
                        $checkTxt3 = true;
                      }
          
                      $exArr1 =  explode(".",$Row1["choice_1"]);
          
                      if($checkTxt1){
                        $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                        $choice[0]["text"] = '';
                        $choice[0]["type_choice"] = "text";
                      }
                      else{
                        if($exArr1[1]){
                          $choice[0]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_1"];
                          $choice[0]["text"] = '';
                          if($exArr1[1] == "mp4"){
                            $choice[0]["type_choice"] = "video";
                          }
                          else{
                            $choice[0]["type_choice"] = "picture";
                          }
                        }
                        else{
                          $choice[0]["image"] = '';
                          $choice[0]["text"] = '';
                          $choice[0]["type_choice"] = "";
                        }
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
          
                      $choice[0]["bg_color"] = $Row1["choice_1_bg"];
                      $choice[0]["txt_color"] = $Row1["choice_1_txt_color"];
          
                      $choice[0]["ismyLike"] = false;
                      foreach($myLike as $Islike){
                        if($Islike == "0"){
                          $choice[0]["ismyLike"] = true;
                        }
                      }
          
                      $choice[0]["ismyVote"] = false;
          
                      if($RowChoiceVote['selector'] == '0'){
                        $choice[0]["ismyVote"] = true;
                      }
          
                      if($Row1["choice_2"] != '' || $Row1['choice_2_textImg'] != 0){
                        $exArr2 =  explode(".",$Row1["choice_2"]);
          
                        if($checkTxt2){
                          $choice[1]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[1];
                          $choice[1]["text"] = '';
                          $choice[1]["type_choice"] = "text";
                        }
                        else{
                          if($exArr2[1]){
                            $choice[1]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_2"];
                            $choice[1]["text"] = '';
                            if($exArr2[1] == "mp4"){
                              $choice[1]["type_choice"] = "video";
                            }
                            else{
                              $choice[1]["type_choice"] = "picture";
                            }
                          }
                          else{
                            $choice[1]["text"] = '';
                            $choice[1]["image"] = '';
                            $choice[1]["type_choice"] = "";
                          }
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
          
                        $choice[1]["bg_color"] = $Row1["choice_2_bg"];
                        $choice[1]["txt_color"] = $Row1["choice_2_txt_color"];
          
                        $choice[1]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == "1"){
                            $choice[1]["ismyLike"] = true;
                          }
                        }
          
                        $choice[1]["ismyVote"] = false;
          
                        if($RowChoiceVote['selector'] == '1'){
                          $choice[1]["ismyVote"] = true;
                        }
                      }
          
                      if($Row1["choice_3"] != '' || $Row1['choice_3_textImg'] != 0){
                        $choice[2]["ismyVote"] = false;
          
                        $exArr3 =  explode(".",$Row1["choice_3"]);
          
                        if($checkTxt3){
                          $choice[2]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[2];
                          $choice[2]["text"] = '';
                          $choice[2]["type_choice"] = "text";
                        }
                        else{
                          if($exArr3[1]){
                            $choice[2]["image"] = SITE_URL."uploads/posts/images/".$Row1["choice_3"];
                            $choice[2]["text"] = '';
                            if($exArr3[1] == "mp4"){
                              $choice[2]["type_choice"] = "video";
                            }
                            else{
                              $choice[2]["type_choice"] = "picture";
                            }
                          }
                          else{
                            $choice[2]["text"] = $Row1["choice_3"];
                            $choice[2]["image"] = '';
                            $choice[2]["type_choice"] = "text";
                          }
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
          
                        $choice[2]["bg_color"] = $Row1["choice_3_bg"];
                        $choice[2]["txt_color"] = $Row1["choice_3_txt_color"];
          
                        $choice[2]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == "2"){
                            $choice[2]["ismyLike"] = true;
                          }
                        }
          
                        if($RowChoiceVote['selector'] == '2'){
                          $choice[2]["ismyVote"] = true;
                        }
                      }
            
                      $arrRow["date_start"] = $Row1["date_start"];
                      $arrRow["time_start"] = $Row1["time_start"];
                      $arrRow["date_end"] = $Row1["date_end"];
                      $arrRow["time_end"] = $Row1["time_end"];
                      $arrRow["answer"] = intval($Row1["anwser"]);
            
                      // if($Row1["anwser"] == 1){
                      //   $arrRow["answer"] = 0;
                      // }
                      // elseif($Row1["anwser"] == 2){
                      //   $arrRow["answer"] = 1;
                      // }
                      // elseif($Row1["anwser"] == 3){
                      //   $arrRow["answer"] = 2;
                      // }
            
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
            
                  if($RowPic['choice_txtImg'] != 0){
                    $sqltxt_Img = $mysqli->prepare("SELECT * FROM action_post WHERE post_id = ? AND Status <> 'Delete' ORDER BY choice");
                    $sqltxt_Img->bind_param("i",$Row["id"]);
                    $sqltxt_Img->execute();
                    $querytxt_Img = $sqltxt_Img->get_result();
            
                    while($RowTxt_Img = $querytxt_Img->fetch_assoc()){
                      $TxtImg[] = $RowTxt_Img['picture'];
                    }
            
                    $choice[0]["image"] = SITE_URL."uploads/action_post/images/".$TxtImg[0];
                    $choice[0]["text"] = "";
                    $choice[0]["type_choice"] = "text"; 
                  }
                  else{
                    $exArr1 =  explode(".",$RowPic["picture"]);
                    if($exArr1[1]){
                      $choice[0]["image"] = SITE_URL."uploads/posts/images/".$RowPic["picture"];
                      $choice[0]["text"] = "";
                      if($exArr1[1] == "mp4"){
                        $choice[0]["type_choice"] = "video"; 
                      }
                      else{
                        $choice[0]["type_choice"] = "picture"; 
                      }
                      
                    }
                    else{
                      $choice[0]["image"] = "";
                      $choice[0]["text"] = $RowPic["picture"];
                      $choice[0]["type_choice"] = "text"; 
                    }
                  }
            
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
                  $arrRow["date_start"] = "";
                  $arrRow["time_start"] = "";
                  $arrRow["date_end"] = "";
                  $arrRow["time_end"] = "";
                }
            
                //////////////////////////////////////////////////////////////////////////////////////////////
            
                if($Row['staff_id'] > 0){
                  $sqluser = $mysqli->prepare("SELECT * FROM staff WHERE id = ? LIMIT 1");
                  $sqluser->bind_param("i",$Row["staff_id"]);
                  $sqluser->execute();
                  $queryUser = $sqluser->get_result();
            
                  if($queryUser->num_rows>0){
                    $RowUser = $queryUser->fetch_assoc();
                    $arrDataUser = array();
            
                    $arrDataUser["id"] = $RowUser["id"];
                    $arrDataUser["facebook_id"] = "";
                    $arrDataUser["gmail_id"] = "";
                    $arrDataUser["apple_id"] = "";
                    $arrDataUser["access_token"] = $RowUser["access_token"];
                    $arrDataUser["avatar"] = SITE_URL."uploads/staff/images/".$RowUser["avatar"];
                    $arrDataUser["cover"] = "";
                    $arrDataUser["username"] = $RowUser["username"];
                    $arrDataUser["first_name"] = $RowUser["fullname"];
                    $arrDataUser["last_name"] = "";
                    $arrDataUser["gender"] = "";
                    $arrDataUser["gender_other"] = "";
                    $arrDataUser["birthdate"] = "";
                    $arrDataUser["telephone"] = $RowUser["telephone"];
                    $arrDataUser["email"] = $RowUser["email"];
                    $arrDataUser["facebook"] = "";
                    $arrDataUser["line"] = "";
                    $arrDataUser["address"] = $RowUser["address"];
                    $arrDataUser["country_id"] = "";
                    $arrDataUser["country_other"] = "";
                    $arrDataUser["province_id"] = $RowUser["province_id"];
                    $arrDataUser["province_other"] = "";
                    $arrDataUser["occupation_id"] = "";
                    $arrDataUser["work_address"] = "";
                    $arrDataUser["about_me"] = "";
                    $arrDataUser["status"] = $RowUser["status"];
                    $arrDataUser["createdate"] = $RowUser["createdate"];
                    $arrDataUser["create_ip"] = $RowUser["create_ip"];
                    $arrDataUser["updatedate"] = $RowUser["updatedate"];
                    $arrDataUser["update_ip"] = $RowUser["update_ip"];
                    $arrDataUser["lastlogin"] = $RowUser["lastlogin"];
                    $arrDataUser["lastlogin_ip"] = $RowUser["lastlogin_ip"];
            
                    $arrDataUser["member_title"] = "";
                    $arrDataUser['member_coin'] = 0;
                    $arrDataUser['member_icon'] = "";
            
                    $arrRow["publisher"] = $arrDataUser;
                  }
                  else{
                    $arrRow["publisher"] = null;
                  }
                }
                else{
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
            
                    $sqlclass_user = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON a.class_name = b.title_en WHERE user_id = ? ");
                    $sqlclass_user->bind_param("i",$Row["user_id"]);
                    $sqlclass_user->execute();
                    $queryClass_User = $sqlclass_user->get_result();
            
                    if($queryClass_User->num_rows>0){
                      $RowClass_User = $queryClass_User->fetch_assoc();
            
                      switch($ac_lang){
                        case "en":
                          $arrDataUser["member_title"] = $RowClass_User["title_en"];
                          break;
                        case "th":
                          $arrDataUser["member_title"] = $RowClass_User["title_th"];
                          break;
                
                        default:
                          $arrDataUser["member_title"] = $RowClass_User["title_th"];
                      }
                      $arrDataUser["member_coin"] = $RowClass_User["point"];
                      $arrDataUser["member_icon"] = SITE_URL."uploads/user_level/images/".$RowClass_User["picture"];
                    }
                    else{
                      $arrDataUser["member_title"] = "";
                      $arrDataUser['member_coin'] = 0;
                      $arrDataUser['member_icon'] = "";
                    }
          
                    $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
                    $sql_publisher_following->bind_param("i",$RowUser["id"]);
                    $sql_publisher_following->execute();
                    $query_publisher_following = $sql_publisher_following->get_result();
                    $Row_publisher_following = $query_publisher_following->fetch_assoc();
          
                    $arrDataUser['following_user'] = $Row_publisher_following['id'];
          
                    //////////////////////////////////////////////////////////////////////////////////
          
                    $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
                    $sql_publisher_follower->bind_param("i",$RowUser["id"]);
                    $sql_publisher_follower->execute();
                    $query_publisher_follower = $sql_publisher_follower->get_result();
                    $Row_publisher_follower = $query_publisher_follower->fetch_assoc();
          
                    $arrDataUser['follower_user'] = $Row_publisher_follower['id'];
          
                    ///////////////////////////////////////////////////////////////////////////////////
          
                    $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                    $sql_publisher_friend->bind_param("i",$RowUser["id"]);
                    $sql_publisher_friend->execute();
                    $query_publisher_friend = $sql_publisher_friend->get_result();
                    $Row_publisher_friend = $query_publisher_friend->fetch_assoc();
          
                    $arrDataUser['friend_user'] = $Row_publisher_friend['id'];
          
                    ///////////////////////////////////////////////////////////////////////////////////
          
                    $sql_publisher_friend_mutual = $mysqli->prepare("SELECT COUNT(id) FROM friend WHERE user_id IN (SELECT friend_id FROM `friend` WHERE user_id IN (SELECT friend_id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id <> ? AND request_action = 'Accept' AND Status <> 'Delete') AND friend_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                    $sql_publisher_friend_mutual->bind_param("iii",$RowUser["id"],$RowUser["id"],$RowUser["id"]);
                    $sql_publisher_friend_mutual->execute();
                    $query_publisher_friend_mutual = $sql_publisher_friend_mutual->get_result();
                    $Row_publisher_friend_mutual = $query_publisher_friend_mutual->fetch_assoc();
          
                    $arrDataUser['friend_mutual_user'] = $Row_publisher_friend_mutual['COUNT(id)'];
          
                    ///////////////////////////////////////////////////////////////////////////////////
            
                    $arrRow["publisher"] = $arrDataUser;
                  }
                  else{
                    $arrRow["publisher"] = null;
                  }
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
                    switch($ac_lang){
                      case "en":
                        $ArrTopic["name"] = $RowInterest["name_en"];
                        break;
            
                      case "th":
                        $ArrTopic["name"] = $RowInterest["name_th"];
                        break;
            
                      default:
                        $ArrTopic["name"] = $RowInterest["name_th"];
                    }
            
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
                    $array_userTag["facebook_id"] = $RowUserTag["facebook_id"];
                    $array_userTag["gmail_id"] = $RowUserTag["gmail_id"];
                    $array_userTag["apple_id"] = $RowUserTag["apple_id"];
                    $array_userTag["access_token"] = $RowUserTag["access_token"];
                    $array_userTag["avatar"] = $RowUserTag["avatar"];
                    $array_userTag["cover"] = $RowUserTag["cover"];
                    $array_userTag["username"] = $RowUserTag["username"];
                    $array_userTag["first_name"] = $RowUserTag["first_name"];
                    $array_userTag["last_name"] = $RowUserTag["last_name"];
                    $array_userTag["gender"] = $RowUserTag["gender"];
                    $array_userTag["gender_other"] = $RowUserTag["gender_other"];
                    $array_userTag["birthdate"] = $RowUserTag["birthdate"];
                    $array_userTag["telephone"] = $RowUserTag["telephone"];
                    $array_userTag["email"] = $RowUserTag["email"];
                    $array_userTag["facebook"] = $RowUserTag["facebook"];
                    $array_userTag["line"] = $RowUserTag["line"];
                    $array_userTag["address"] = $RowUserTag["address"];
                    $array_userTag["country_id"] = $RowUserTag["country_id"];
                    $array_userTag["country_other"] = $RowUserTag["country_other"];
                    $array_userTag["province_id"] = $RowUserTag["province_id"];
                    $array_userTag["province_other"] = $RowUserTag["province_other"];
                    $array_userTag["occupation_id"] = $RowUserTag["occupation_id"];
                    $array_userTag["work_address"] = $RowUserTag["work_address"];
                    $array_userTag["about_me"] = $RowUserTag["about_me"];
                    $array_userTag["status"] = $RowUserTag["status"];
                    $array_userTag["createdate"] = $RowUserTag["createdate"];
                    $array_userTag["create_ip"] = $RowUserTag["create_ip"];
                    $array_userTag["updatedate"] = $RowUserTag["updatedate"];
                    $array_userTag["update_ip"] = $RowUserTag["update_ip"];
                    $array_userTag["lastlogin"] = $RowUserTag["lastlogin"];
                    $array_userTag["lastlogin_ip"] = $RowUserTag["lastlogin_ip"];
            
                    $sqlclass_user1 = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON a.class_name = b.title_en WHERE user_id = ? ");
                    $sqlclass_user1->bind_param("i",$Row["user_id"]);
                    $sqlclass_user1->execute();
                    $queryClass_User1 = $sqlclass_user1->get_result();
            
                    if($queryClass_User1->num_rows>0){
                      $RowClass_User1 = $queryClass_User1->fetch_assoc();
            
                      switch($ac_lang){
                        case "en":
                          $array_userTag["member_title"] = $RowClass_User1["title_en"];
                          break;
                        case "th":
                          $array_userTag["member_title"] = $RowClass_User1["title_th"];
                          break;
                
                        default:
                          $array_userTag["member_title"] = $RowClass_User1["title_th"];
                      }
                      $array_userTag["member_coin"] = $RowClass_User1["point"];
                      $array_userTag["member_icon"] = SITE_URL."uploads/user_level/images/".$RowClass_User1["picture"];
                    }
                    else{
                      $array_userTag["member_title"] = "";
                      $array_userTag['member_coin'] = 0;
                      $array_userTag['member_icon'] = "";
                    }
          
                    $sql_publisher_following = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE user_id = ? AND Status <> 'Delete' ");
                    $sql_publisher_following->bind_param("i",$RowUser["id"]);
                    $sql_publisher_following->execute();
                    $query_publisher_following = $sql_publisher_following->get_result();
                    $Row_publisher_following = $query_publisher_following->fetch_assoc();
          
                    $arrDataUser['following_user'] = $Row_publisher_following['id'];
          
                    //////////////////////////////////////////////////////////////////////////////////
          
                    $sql_publisher_follower = $mysqli->prepare("SELECT COUNT(id) as id FROM friend_follow WHERE follow_id = ? AND Status <> 'Delete' ");
                    $sql_publisher_follower->bind_param("i",$RowUser["id"]);
                    $sql_publisher_follower->execute();
                    $query_publisher_follower = $sql_publisher_follower->get_result();
                    $Row_publisher_follower = $query_publisher_follower->fetch_assoc();
          
                    $arrDataUser['follower_user'] = $Row_publisher_follower['id'];
          
                    ///////////////////////////////////////////////////////////////////////////////////
          
                    $sql_publisher_friend = $mysqli->prepare("SELECT COUNT(id) as id FROM friend WHERE user_id = ? AND request_action = 'Accept' AND Status <> 'Delete' ");
                    $sql_publisher_friend->bind_param("i",$RowUser["id"]);
                    $sql_publisher_friend->execute();
                    $query_publisher_friend = $sql_publisher_friend->get_result();
                    $Row_publisher_friend = $query_publisher_friend->fetch_assoc();
          
                    $arrDataUser['friend_user'] = $Row_publisher_friend['id'];
          
                    ///////////////////////////////////////////////////////////////////////////////////
            
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
              }
            }
            else{
              $Result['data'] = null;
            }

            $Result['data'] = $arrRow;
          }
          else{
            $Result['message'] = get_responseLang($ac_lang)[19];
          }
        }
    }
    else{
      $Result['message'] = get_responseLang($ac_lang)[12];
    }
    
 
    
  }
  else{
    $Result['message'] = get_responseLang($ac_lang)[15];
  }

}
else{
  $Result['message'] = get_responseLang($ac_lang)[14];
}

echo json_encode($Result);

?>