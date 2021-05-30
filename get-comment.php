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


if(!empty($_POST['post_id'])){
    $mysqli     =   db_connect();
    $ac_lang = get_headerLang();

    if($mysqli){
      $post_id = $_POST['post_id'];
      $myid = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT id,comment,comment_pic,user_comment,CreateDate FROM post_comment WHERE post_id = ? AND user_comment <> 0 AND Status <> 'Delete' ");
      $sql->bind_param("i", $post_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        while($Row = $query->fetch_assoc()){
          $comment_arr["id"] = $Row["id"];
          $comment_arr["text"] = $Row["comment"];
          if($Row["comment_pic"] == ""){
            $comment_arr["comment_picture"] = "";
          }
          else{
            $comment_arr["comment_picture"] = $Row["comment_pic"];
          }
          
          $comment_arr["createdate"] = $Row["CreateDate"];

          $thisTime = new DateTime('now');
          $itemTime = new DateTime($Row["CreateDate"]);
          $nowTime = $thisTime->diff($itemTime);
          $TimeDay = $nowTime->format('%a');
          $HourDay = $nowTime->format('%h');

          if($TimeDay > '0'){
            if($ac_lang == 'th'){
              $passTime = $nowTime->format('%a วัน');
            }
            else{
              $passTime = $nowTime->format('%a Day');
            }
          }
          elseif($HourDay > '0'){
            if($ac_lang == 'th'){
              $passTime = $nowTime->format('%h ชั่วโมง');
            }
            else{
              $passTime = $nowTime->format('%h Hour');
            } 
          }
          else{
            if($ac_lang == 'th'){
              $passTime = $nowTime->format('%m นาที');
            }
            else{
              $passTime = $nowTime->format('%m Min');
            } 
          }

          $comment_arr["PassDate"] = $passTime;

          $comment_like = $mysqli->prepare("SELECT COUNT(user_like) as comment_like FROM post_comment_like  WHERE comment_id = ? AND user_like <> 0 AND Status <> 'Delete' ");
          $comment_like->bind_param("i",$Row["id"]);
          $comment_like->execute();
          $query_commentLike  =   $comment_like->get_result();
          $Row_commentLike = $query_commentLike->fetch_assoc();
          $comment_arr["comment_like"] = $Row_commentLike['comment_like'];

          /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

          $mylike1 = $mysqli->prepare("SELECT * FROM post_comment_like WHERE user_like = ? AND comment_id = ? AND Status <> 'Delete' ");
          $mylike1->bind_param("ii",$myid,$Row["id"]);
          $mylike1->execute();
          $query_like1  =   $mylike1->get_result();

          if($query_like1->num_rows > 0){
            $comment_arr["is_mylike"] = true;
          }
          else{
            $comment_arr["is_mylike"] = false;
          }

          /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          $Get_user = get_user($Row["user_comment"]);
          $comment_arr["user_comment"] = $Get_user;

          $sql2 = $mysqli->prepare("SELECT * FROM post_comment_relative WHERE comment_id = ? AND Status <> 'Delete' ");
          $sql2->bind_param("i",$Row["id"]);
          $sql2->execute();
          $query2  =   $sql2->get_result();
          if($query2->num_rows > 0){
            $Array_relative_comment = array();

            while($Row2 = $query2->fetch_assoc()){
              $arr_comment_relative["id"] = $Row2["id"];
              $arr_comment_relative["text"] = $Row2["text"];

              if($Row2["pic"] == ""){
                $arr_comment_relative["comment_reply_picture"] = "";
              }
              else{
                $arr_comment_relative["comment_reply_picture"] = $Row2["pic"];
              }

              $arr_comment_relative["createdate"] = $Row2["CreateDate"];

              $thisTime2 = new DateTime('now');
              $itemTime2 = new DateTime($Row2["CreateDate"]);
              $nowTime2 = $thisTime2->diff($itemTime2);
              $TimeDay2 = $nowTime2->format('%a');
              $HourDay2 = $nowTime2->format('%h');

              if($TimeDay2 > '0'){
                if($ac_lang == 'th'){
                  $passTime2 = $nowTime2->format('%a วัน');
                }
                else{
                  $passTime2 = $nowTime2->format('%a Day');
                }
              }
              elseif($HourDay2 > '0'){
                if($ac_lang == 'th'){
                  $passTime2 = $nowTime2->format('%h ชั่วโมง');
                }
                else{
                  $passTime2 = $nowTime2->format('%h Hour');
                } 
              }
              else{
                if($ac_lang == 'th'){
                  $passTime2 = $nowTime2->format('%m นาที');
                }
                else{
                  $passTime2 = $nowTime2->format('%m Min');
                } 
              }

              $arr_comment_relative["PassDate_reply"] = $passTime2;

  
              $slq_replyLike = $mysqli->prepare("SELECT COUNT(user_like) as reply_like FROM post_comment_relative_like WHERE reply_comment_id = ? AND user_like <> 0 AND Status <> 'Delete' ");

              $slq_replyLike->bind_param("i",$Row2["id"]);

              $slq_replyLike->execute();

              $query_replyLike  =   $slq_replyLike->get_result();

              $Row_replyLike = $query_replyLike->fetch_assoc();

  

              $arr_comment_relative["reply_like"] = $Row_replyLike['reply_like'];



              /////////////////////////////////////////////////////////////////////////////////////////////////////////////////



              $mylike2 = $mysqli->prepare("SELECT * FROM post_comment_relative_like WHERE user_like = ? AND reply_comment_id = ? AND Status <> 'Delete' ");

              $mylike2->bind_param("ii",$myid,$Row2["id"]);

              $mylike2->execute();

              $query_like2  =   $mylike2->get_result();



              if($query_like2->num_rows > 0){

                $arr_comment_relative["is_mylike"] = true;

              }

              else{

                $arr_comment_relative["is_mylike"] = false;

              }

              /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

              $Get_user_relative = get_user($Row2["user_id"]);
              $arr_comment_relative["user_reply"] = $Get_user_relative;



              $Array_relative_comment[] = $arr_comment_relative;

            }

            $comment_arr["reply"] = $Array_relative_comment;

          }
          else{
            $comment_arr["reply"] = null;
          }

          $FaarRow[] = $comment_arr;

          $Result['total_found'] += 1;
        }


        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';
        $Result['data'] = $FaarRow; 
      }
      else{
        $Result['message'] = 'No Found Comment';
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