<?php
header("Content-Type: application/json");

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
      $choice = $_POST['choice'];
      $myid = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT * FROM post_choice_comment_user WHERE post_id = ? AND choice_comment = ? AND Status <> 'Delete' ");
      $sql->bind_param("ii", $post_id,$choice);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        while($Row = $query->fetch_assoc()){
          // $Get_user = get_user($Row["user_id"]);
          
          $arr_data["id"] = $Row["id"];
          $arr_data["choice"] = $Row["choice_comment"];
          $arr_data["text"] = $Row["text"];
          if($Row["picture"] == ""){
            $arr_data["comment_picture"] = "";
          }
          else{
            $arr_data["comment_picture"] = $Row["picture"];
          }
          
          $arr_data["createdate"] = $Row["CreateDate"];

          //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

          $arr_data["PassDate"] = $passTime;

          //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          $sqllike = $mysqli->prepare("SELECT COUNT(id) FROM post_choice_comment_like WHERE choice_comment_id = ? AND Status <> 'Delete' ");
          $sqllike->bind_param("i", $Row["id"]);
          $sqllike->execute();
          $querylike  =   $sqllike->get_result();
          $Rowlike = $querylike->fetch_assoc();

          $arr_data["comment_like"] = $Rowlike['COUNT(id)'];
          //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

          $mylike1 = $mysqli->prepare("SELECT * FROM post_choice_comment_like WHERE user_id = ? AND choice_comment_id = ? AND Status <> 'Delete' ");
          $mylike1->bind_param("ii",$myid,$Row["id"]);
          $mylike1->execute();
          $query_like1  =   $mylike1->get_result();

          if($query_like1->num_rows > 0){
            $arr_data["is_mylike"] = true;
          }
          else{
            $arr_data["is_mylike"] = false;
          }


          //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          $reply_arr = array();

          $Get_user = get_user($Row["user_id"]);
          if($Get_user == ""){
            $Get_user = null;
          }
          $arr_data["user_comment"] = $Get_user;

          $sql_reply = $mysqli->prepare("SELECT * FROM post_choice_comment_reply WHERE choice_comment_id = ? AND Status <> 'Delete' ");
          $sql_reply->bind_param("i", $Row["id"]);
          $sql_reply->execute();
          $query_reply = $sql_reply->get_result();
          if($query_reply->num_rows > 0){
            while($Row_reply = $query_reply->fetch_assoc()){
              $reply['id'] = $Row_reply['id'];
              $reply['text'] = $Row_reply['text'];

              if($Row_reply["picture"] == ""){
                $reply["comment_reply_picture"] = "";
              }
              else{
                $reply["comment_reply_picture"] = $Row_reply["picture"];
              }

              $reply['CreateDate'] = $Row_reply['CreateDate'];
  
              $thisTime1 = new DateTime('now');
              $itemTime1 = new DateTime($Row_reply["CreateDate"]);
              $nowTime1 = $thisTime1->diff($itemTime1);
              $TimeDay2 = $nowTime1->format('%a');
              $HourDay2 = $nowTime1->format('%h');
              if($TimeDay2 > '0'){
                if($ac_lang == 'th'){
                  $passTime1 = $nowTime1->format('%a วัน');
                }
                else{
                  $passTime1 = $nowTime1->format('%a Day');
                }
              }
              elseif($HourDay2 > '0'){
                if($ac_lang == 'th'){
                  $passTime1 = $nowTime1->format('%h ชั่วโมง');
                }
                else{
                  $passTime1 = $nowTime1->format('%h Hour');
                } 
              }
              else{
                if($ac_lang == 'th'){
                  $passTime1 = $nowTime1->format('%m นาที');
                }
                else{
                  $passTime1 = $nowTime1->format('%m Min');
                } 
              }
              $reply["PassDate_reply"] = $passTime1;
  
              $sql_replylike = $mysqli->prepare("SELECT COUNT(id) FROM post_choice_comment_reply_like WHERE reply_id = ? AND Status <> 'Delete' ");
              $sql_replylike->bind_param("i", $Row_reply['id']);
              $sql_replylike->execute();
              $query_replylike = $sql_replylike->get_result();
              $Row_replylike = $query_replylike->fetch_assoc();
  
              $reply['reply_like'] = $Row_replylike['COUNT(id)'];

              /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

              $mylike2 = $mysqli->prepare("SELECT * FROM post_choice_comment_reply_like WHERE user_id = ? AND reply_id = ? AND Status <> 'Delete' ");
              $mylike2->bind_param("ii",$myid,$Row_reply["id"]);
              $mylike2->execute();
              $query_like2  =   $mylike2->get_result();

              if($query_like2->num_rows > 0){
                $reply["is_mylike"] = true;
              }
              else{
                $reply["is_mylike"] = false;
              }

              /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

              $Get_user1 = get_user($Row_reply["user_id"]);
              if($Get_user1 == ""){
                $Get_user1 = null;
              }
              $reply["user_reply"] = $Get_user1;
  
              $reply_arr[] = $reply;
            }
            $arr_data["reply"] = $reply_arr;
          }
          else{
            $arr_data["reply"] = null;
          }

          //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          $arr_data["user_comment"] = $Get_user;
          
          $FaarRow[] = $arr_data;
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