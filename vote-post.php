<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';

if(!empty($_POST['post_id']) && !empty($_POST['user_id'])){
  $mysqli     =   db_connect();

  if($mysqli){
    $post_id = $_POST['post_id'];
    $myid = $_POST['user_id'];
    $createdate = date("Y-m-d H:i:s");
    $create_ip = $_SERVER["REMOTE_ADDR"];
    $vote = intval($_POST["vote"]);
    $status = "Enable";
    $timeout = false;
    $showData = false;

    $presql = $mysqli->prepare("SELECT * FROM post WHERE id = ? ");
    $presql->bind_param("i",$post_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      $PreRow = $prequery->fetch_assoc();

        ///////////////////////////////////////////////////

        if($PreRow["type_vote"] == 'vote'){
          $presql_post = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete'");
          $presql_post->bind_param("i",$post_id);
          $presql_post->execute();
          $prequery_post = $presql_post->get_result();
          $PreRow_Post = $prequery_post->fetch_assoc();

          $prepareTime = date($PreRow_Post["date_end"]." ".$PreRow_Post["time_end"]);

          if(date("Y-m-d H:i:s") > $prepareTime){
            $timeout = true;
          }
        }
        elseif($PreRow["type_vote"] == 'quiz'){
          $presql_post = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete'");
          $presql_post->bind_param("i",$post_id);
          $presql_post->execute();
          $prequery_post = $presql_post->get_result();
          $PreRow_Post = $prequery_post->fetch_assoc();

          $prepareTime = $PreRow_Post["date_end"]." ".$PreRow_Post["time_end"];

          if(date("Y-m-d H:i:s") > $prepareTime){
            $timeout = true;
          }
        }

        ///////////////////////////////////////////////////

        if($timeout == false){
          $presql_meta = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete'");
          $presql_meta->bind_param("ii",$post_id,$myid);
          $presql_meta->execute();
          $prequery_meta = $presql_meta->get_result();
    
          if($prequery_meta->num_rows == 0){
            $sql = $mysqli->prepare("INSERT INTO post_meta_user(user_id,post_id,selector,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
            $sql->bind_param("iiisss",$myid,$post_id,$vote,$createdate,$create_ip,$status);
            if($sql->execute()){
      
              if($PreRow["type_vote"] == 'vote'){
                $presql2 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? ");
                $presql2->bind_param("i",$post_id);
                $presql2->execute();
                $prequery2 = $presql2->get_result();
                $PreRow2 = $prequery2->fetch_assoc();
      
                if($vote == '0'){
                  $sum_vote = $PreRow2["choice_1_result"]+1;
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Insert Success';
                  $showData = true;
                  
                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_1_result'];
                }
                elseif($vote == '1'){
                  $sum_vote = $PreRow2["choice_2_result"]+1;
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Insert Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_2_result'];
                }
                elseif($vote == '2'){
                  $sum_vote = $PreRow2["choice_3_result"]+1;
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_3_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Insert Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_3_result'];
                }
                else{
                  $Result['message'] = 'Error Something';
                }
              }
              elseif($PreRow["type_vote"] == 'quiz'){
                $presql2 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? ");
                $presql2->bind_param("i",$post_id);
                $presql2->execute();
                $prequery2 = $presql2->get_result();
                $PreRow2 = $prequery2->fetch_assoc();
      
                if($vote == '0'){
                  $sum_vote = $PreRow2["choice_1_result"]+1;
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_1_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Insert Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_1_result'];
                }
                elseif($vote == '1'){
                  $sum_vote = $PreRow2["choice_2_result"]+1;
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_2_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Insert Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_2_result'];
                }
                elseif($vote == '2'){
                  $sum_vote = $PreRow2["choice_3_result"]+1;
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_3_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Insert Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_3_result'];
                }
                else{
                  $Result['message'] = 'Error Something';
                }
              }
              else{
                $Result['message'] = 'Error dont have type vote';
              }
              
            }
            else{
              $Result['message'] = 'Cant Insert Data';
            }
          }
          else{
            $status = "Delete";
            $RowPre_meta = $prequery_meta->fetch_assoc();

            $sql = $mysqli->prepare("UPDATE post_meta_user SET Status=? WHERE post_id = ? AND user_id = ?");
            $sql->bind_param("sii",$status,$post_id,$myid);
            if($sql->execute()){

              $sql_checkC = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status = 'Delete' ORDER BY id DESC LIMIT 1");
              $sql_checkC->bind_param("ii",$post_id,$myid);
              $sql_checkC->execute();
              $prequery_check = $sql_checkC->get_result();
              $Row_Check = $prequery_check->fetch_assoc();

              if($PreRow["type_vote"] == 'vote'){
                $presql2 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? ");
                $presql2->bind_param("i",$post_id);

                if($presql2->execute()){
                  $prequery2 = $presql2->get_result();
                  $PreRow2 = $prequery2->fetch_assoc();

                  if($Row_Check['selector'] == '0'){
                    if($PreRow2["choice_1_result"] > 0){
                      $sum_vote = $PreRow2["choice_1_result"]-1;
                    }
                    else{
                      $sum_vote = 0;
                    }
        
                    $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_result=? WHERE post_id=?");
                    $sql2->bind_param("ii",$sum_vote,$post_id);
                    $sql2->execute();
        
                    $Result['status'] = 'Success';
                    $Result['message'] = 'Unvote Success';
                    $showData = true;
  
                    $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                    $sql3->bind_param("i",$post_id);
                    $sql3->execute();
                    $query3 = $sql3->get_result();
                    $Row3 = $query3->fetch_assoc();
  
                    $Result['number_vote'] = $Row3['choice_1_result'];
                  }
                  elseif($Row_Check['selector'] == '1'){
                    if($PreRow2["choice_2_result"] > 0){
                      $sum_vote = $PreRow2["choice_2_result"]-1;
                    }
                    else{
                      $sum_vote = 0;
                    }
        
                    $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_result=? WHERE post_id=?");
                    $sql2->bind_param("ii",$sum_vote,$post_id);
                    
                    if($sql2->execute()){
                      $Result['status'] = 'Success';
                      $Result['message'] = 'UnVote Success';
                      $showData = true;

                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();

                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_2_result'];
                    }
                    else{
                      $Result['message'] = 'Error Final Something';
                    }
                  }
                  elseif($Row_Check['selector'] == '2'){
                    if($PreRow2["choice_3_result"] > 0){
                      $sum_vote = $PreRow2["choice_3_result"]-1;
                    }
                    else{
                      $sum_vote = 0;
                    }  
        
                    $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_3_result=? WHERE post_id=?");
                    $sql2->bind_param("ii",$sum_vote,$post_id);
                    $sql2->execute();
        
                    $Result['status'] = 'Success';
                    $Result['message'] = 'UnVote Success';
                    $showData = true;
  
                    $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                    $sql3->bind_param("i",$post_id);
                    $sql3->execute();
                    $query3 = $sql3->get_result();
                    $Row3 = $query3->fetch_assoc();
  
                    $Result['number_vote'] = $Row3['choice_3_result'];
                  }
                }
                else{
                  $Result['message'] = 'Not Found Post meta';
                }
                
              }
              elseif($PreRow["type_vote"] == 'quiz'){
                $presql2 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? ");
                $presql2->bind_param("i",$post_id);
                $presql2->execute();
                $prequery2 = $presql2->get_result();
                $PreRow2 = $prequery2->fetch_assoc();
      
                if($Row_Check['selector'] == '0'){
                  if($PreRow2["choice_1_result"] > 0){
                    $sum_vote = $PreRow2["choice_1_result"]-1;
                  }
                  else{
                    $sum_vote = 0;
                  }
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_1_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'UnVote Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_1_result'];
                }
                elseif($Row_Check['selector'] == '1'){
                  if($PreRow2["choice_2_result"] > 0){
                    $sum_vote = $PreRow2["choice_2_result"]-1;
                  }
                  else{
                    $sum_vote = 0;
                  }
                  
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_2_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Unvote Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_2_result'];
                }
                elseif($Row_Check['selector'] == '2'){
                  if($PreRow2["choice_3_result"] > 0){
                    $sum_vote = $PreRow2["choice_3_result"]-1;
                  }
                  else{
                    $sum_vote = 0;
                  }
      
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_3_result=? WHERE post_id=?");
                  $sql2->bind_param("ii",$sum_vote,$post_id);
                  $sql2->execute();
      
                  $Result['status'] = 'Success';
                  $Result['message'] = 'Unvote Success';
                  $showData = true;

                  $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                  $sql3->bind_param("i",$post_id);
                  $sql3->execute();
                  $query3 = $sql3->get_result();
                  $Row3 = $query3->fetch_assoc();

                  $Result['number_vote'] = $Row3['choice_3_result'];
                }
              }

              ///////////////////////////////////////////////////////////////////////////

              if($PreRow['number_anwser'] > 1 && $Row_Check['selector'] != $vote){
                $status = 'Enable';

                $sql_ins = $mysqli->prepare("INSERT INTO post_meta_user(user_id,post_id,selector,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
                $sql_ins->bind_param("iiisss",$myid,$post_id,$vote,$createdate,$create_ip,$status);
                if($sql_ins->execute()){
                  if($PreRow["type_vote"] == 'vote'){
                    $presql2 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? ");
                    $presql2->bind_param("i",$post_id);
                    $presql2->execute();
                    $prequery2 = $presql2->get_result();
                    $PreRow2 = $prequery2->fetch_assoc();
          
                    if($vote == '0'){
                      $sum_vote = $PreRow2["choice_1_result"]+1;
          
                      $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_result=? WHERE post_id=?");
                      $sql2->bind_param("ii",$sum_vote,$post_id);
                      $sql2->execute();
          
                      $Result['status'] = 'Success';
                      $Result['message'] = 'Insert Success';
                      $showData = true;
                      
                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();
                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_1_result'];
                    }
                    elseif($vote == '1'){
                      $sum_vote = $PreRow2["choice_2_result"]+1;
          
                      $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_result=? WHERE post_id=?");
                      $sql2->bind_param("ii",$sum_vote,$post_id);
                      $sql2->execute();
          
                      $Result['status'] = 'Success';
                      $Result['message'] = 'Insert Success';
                      $showData = true;
    
                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();
                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_2_result'];
                    }
                    elseif($vote == '2'){
                      $sum_vote = $PreRow2["choice_3_result"]+1;
          
                      $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_3_result=? WHERE post_id=?");
                      $sql2->bind_param("ii",$sum_vote,$post_id);
                      $sql2->execute();
          
                      $Result['status'] = 'Success';
                      $Result['message'] = 'Insert Success';
                      $showData = true;
    
                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();
                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_3_result'];
                    }
                    else{
                      $Result['message'] = 'Error Something';
                    }
                  }
                  elseif($PreRow["type_vote"] == 'quiz'){
                    $presql2 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? ");
                    $presql2->bind_param("i",$post_id);
                    $presql2->execute();
                    $prequery2 = $presql2->get_result();
                    $PreRow2 = $prequery2->fetch_assoc();
          
                    if($vote == '0'){
                      $sum_vote = $PreRow2["choice_1_result"]+1;
          
                      $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_1_result=? WHERE post_id=?");
                      $sql2->bind_param("ii",$sum_vote,$post_id);
                      $sql2->execute();
          
                      $Result['status'] = 'Success';
                      $Result['message'] = 'Insert Success';
                      $showData = true;
    
                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();
                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_1_result'];
                    }
                    elseif($vote == '1'){
                      $sum_vote = $PreRow2["choice_2_result"]+1;
          
                      $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_2_result=? WHERE post_id=?");
                      $sql2->bind_param("ii",$sum_vote,$post_id);
                      $sql2->execute();
          
                      $Result['status'] = 'Success';
                      $Result['message'] = 'Insert Success';
                      $showData = true;
    
                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();
                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_2_result'];
                    }
                    elseif($vote == '2'){
                      $sum_vote = $PreRow2["choice_3_result"]+1;
          
                      $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_3_result=? WHERE post_id=?");
                      $sql2->bind_param("ii",$sum_vote,$post_id);
                      $sql2->execute();
          
                      $Result['status'] = 'Success';
                      $Result['message'] = 'Insert Success';
                      $showData = true;
    
                      $sql3 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
                      $sql3->bind_param("i",$post_id);
                      $sql3->execute();
                      $query3 = $sql3->get_result();
                      $Row3 = $query3->fetch_assoc();
    
                      $Result['number_vote'] = $Row3['choice_3_result'];
                    }
                    else{
                      $Result['message'] = 'Error Something';
                    }
                  }
                  else{
                    $Result['message'] = 'Error dont have type vote';
                  }
                }
              }
              
            }
            else{
              $Result['message'] = 'Cant Insert Data';
            }
            
          }


          if($showData){
            //////////////////////////////////////////////////////////////////////////////////////////////////////////
            // เพิ่มการแจ้งเตือน

            $presql_noti = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
            $presql_noti->bind_param("i",$post_id);
            $presql_noti->execute();
            $prequery_noti = $presql_noti->get_result();
            $preRow_noti = $prequery_noti->fetch_assoc();

            $type_noti = "vote";

            $sql_noti = $mysqli->prepare("INSERT INTO noti(type,user_id,my_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?)");
            $sql_noti->bind_param("siisss",$type_noti,$myid,$preRow_noti['user_id'],$createdate,$create_ip,$status);
            $sql_noti->execute();


            //////////////////////////////////////////////////////////////////////////////////////////////////////////

            $Result['status'] = 'Success';
            $Result['message'] = 'Vote Success';

            $sqlF1 = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
            $sqlF1->bind_param("i",$post_id);
            $sqlF1->execute();
            $queryF1  =   $sqlF1->get_result();

            if($queryF1->num_rows>0){
              while($RowF1 = $queryF1->fetch_assoc()){
                $arrRowF1["id"] = $RowF1["id"];
                $arrRowF1["title"] = $RowF1["title"];
                $arrRowF1["post_type"] = $RowF1["type_vote"];
                $arrRowF1["location"] = $RowF1["pin_map"];
                $arrRowF1["post_style"] = $RowF1["type_create_post"];

                $sqllike = $mysqli->prepare("SELECT user_like, user_comment, user_share FROM post_comment WHERE post_id = ? AND Status <> 'Delete' ");
                $sqllike->bind_param("i",$RowF1["id"]);
                $sqllike->execute();
                $queryLike = $sqllike->get_result();

                $countLike = 0;
                $countComment = 0;
                $countShare = 0;

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

                $arrRowF1["likes"] = $countLike;
                $arrRowF1["comments"] = $countComment;
                $arrRowF1["shares"] = $countShare;
                
                ///////////////////////////////////////////////////////////////////////////////////////////////

                $sql_post_like = $mysqli->prepare("SELECT * FROM post_comment WHERE post_id = ? AND user_like = ? AND Status <> 'Delete' ");
                $sql_post_like->bind_param("ii",$Row["id"],$myid);
                $sql_post_like->execute();
                $query_post_Like = $sql_post_like->get_result();

                if($query_post_Like->num_rows > 0){
                  $arrRowF1["post_ismyLike"] = true;
                }
                else{
                  $arrRowF1["post_ismyLike"] = false;
                }

                ///////////////////////////////////////////////////////////////////////////////////////////////

                if($RowF1["type_vote"] == 'vote'){
                  $sql1 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? ");
                  $sql1->bind_param("i",$RowF1["id"]);
                  $sql1->execute();
                  $query1 = $sql1->get_result();

                  if($query1->num_rows>0){
                    while($Row1 = $query1->fetch_assoc()){  
                      $sqlF2 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? AND Status <> 'Delete' ");
                      $sqlF2->bind_param("i",$RowF1["id"]);
                      $sqlF2->execute();
                      $queryF2 = $sqlF2->get_result();
                      $RowF2 = $queryF2->fetch_assoc();

                      $sqlchoice_like = $mysqli->prepare("SELECT * FROM post_choice_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                      $sqlchoice_like->bind_param("ii",$RowF1["id"],$myid);
                      $sqlchoice_like->execute();
                      $queryChoice = $sqlchoice_like->get_result();
                      while($RowChoice = $queryChoice->fetch_assoc()){
                        $myLike[] = $RowChoice['choice_like'];
                      }
                      
                      $choice = array();  

                      //////////////////////////////////////////////////////////////////////////

                      $sqlchoice_vote = $mysqli->prepare("SELECT * FROM post_meta_user WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                      $sqlchoice_vote->bind_param("ii",$RowF1["id"],$myid);
                      $sqlchoice_vote->execute();
                      $queryChoiceVote = $sqlchoice_vote->get_result();
                      $RowChoiceVote = $queryChoiceVote->fetch_assoc();
                      
                      //////////////////////////////////////////////////////////////////////////

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

                      if($RowF2["choice1_like"] > 0){
                        $choice[0]["likes"] =  $RowF2["choice1_like"];
                      }
                      else{
                        $choice[0]["likes"] = 0;
                      }

                      if($RowF2["choice1_comment"] > 0){
                        $choice[0]["comments"] =  $RowF2["choice1_comment"];
                      }
                      else{
                        $choice[0]["comments"] = 0;
                      }

                      if($RowF2["choice1_share"] > 0){
                        $choice[0]["share"] =  $RowF2["choice1_share"];
                      }
                      else{
                        $choice[0]["share"] = 0;
                      }

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

                      if($Row1["choice_2"] != ''){

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

                        if($RowF2["choice2_like"] > 0){
                          $choice[1]["likes"] =  $RowF2["choice2_like"];
                        }
                        else{
                          $choice[1]["likes"] = 0;
                        }

                        if($RowF2["choice2_comment"] > 0){
                          $choice[1]["comments"] =  $RowF2["choice2_comment"];
                        }
                        else{
                          $choice[1]["comments"] = 0;
                        }

                        if($RowF2["choice2_share"] > 0){
                          $choice[1]["share"] =  $RowF2["choice2_share"];
                        }
                        else{
                          $choice[1]["share"] = 0;
                        }

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

                      if($Row1["choice_3"] != ''){
                        $choice[2]["ismyVote"] = false;

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

                        if($RowF2["choice3_like"] > 0){
                          $choice[2]["likes"] =  $RowF2["choice3_like"];
                        }
                        else{
                          $choice[2]["likes"] = 0;
                        }

                        if($RowF2["choice3_comment"] > 0){
                          $choice[2]["comments"] =  $RowF2["choice3_comment"];
                        }
                        else{
                          $choice[2]["comments"] = 0;
                        }

                        if($RowF2["choice3_share"] > 0){
                          $choice[2]["share"] =  $RowF2["choice3_share"];
                        }
                        else{
                          $choice[2]["share"] = 0;
                        }

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

                      $arrRowF1["date_start"] = $Row1["date_start"];
                      $arrRowF1["time_start"] = $Row1["time_start"];
                      $arrRowF1["date_end"] = $Row1["date_end"];
                      $arrRowF1["time_end"] = $Row1["time_end"];

                      ////////////////////////////////////////////////////////////////////////////
                      $arrTime = array();
                      $origin = new DateTime();

                      $timeplus = "'".$Row1["date_start"]." ".$Row1["time_start"]."'";

                      if(date('Y-m-d H:i:s') >= $Row1["date_end"]." ".$Row1["time_end"]){
                        $arrRowF1["post_status"] = "close";
                        $arrRowF1["post_time_remain"] = "";
                      }
                      elseif(date('Y-m-d H:i:s') >= $Row1["date_start"]." ".$Row1["time_start"]){
                        $arrRowF1["post_status"] = "open";
                        $PreTime = new DateTime($Row1["date_end"]);

                        $interval = $origin->diff($PreTime);
                        $Ftime = $interval->format('%a days %h Hours %i Min');

                        $arrRowF1["post_time_remain"] = $Ftime;
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
                          $arrRowF1["post_status"] = "incoming";
                          $arrRowF1["post_time_remain"] = $checktime;
                        }
                        else{
                          $arrRowF1["post_status"] = "normal";
                          $arrRowF1["post_time_remain"] = $Ftime;
                        }

                      }

                      // $arrRow["post_status"] = $arrTime;
                      ////////////////////////////////////////////////////////////////////////////
                      
                    }

                    $arrRowF1["choice"] = $choice;
                  }
                  else{
                    $arrRowF1["choice"] = null;
                  }
                }
                elseif($RowF1["type_vote"] == 'quiz'){
                  $sql1 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? ");
                  $sql1->bind_param("i",$RowF1["id"]);
                  $sql1->execute();
                  $query1 = $sql1->get_result();

                  if($query1->num_rows>0){
                    while($Row1 = $query1->fetch_assoc()){  
                      $sqlF2 = $mysqli->prepare("SELECT * FROM post_choice_meta WHERE post_id = ? ");
                      $sqlF2->bind_param("i",$RowF1["id"]);
                      $sqlF2->execute();
                      $queryF2 = $sqlF2->get_result();
                      $RowF2 = $queryF2->fetch_assoc();

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

                      if($RowF2["choice1_like"] > 0){
                        $choice[0]["likes"] =  $RowF2["choice1_like"];
                      }
                      else{
                        $choice[0]["likes"] = 0;
                      }

                      if($RowF2["choice1_comment"] > 0){
                        $choice[0]["comments"] =  $RowF2["choice1_comment"];
                      }
                      else{
                        $choice[0]["comments"] = 0;
                      }

                      if($RowF2["choice1_share"] > 0){
                        $choice[0]["share"] =  $RowF2["choice1_share"];
                      }
                      else{
                        $choice[0]["share"] = 0;
                      }

                      $choice[0]["ismyLike"] = false;
                      foreach($myLike as $Islike){
                        if($Islike == '0'){
                          $choice[0]["ismyLike"] = true;
                        }
                      }

                      $choice[0]["ismyVote"] = false;
                      if($RowChoiceVote['selector'] == '0'){
                        $choice[0]["ismyVote"] = true;
                      }

                      //////////////////////////////////////////////////////////////////////////////

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

                      if($RowF2["choice2_like"] > 0){
                        $choice[1]["likes"] =  $RowF2["choice2_like"];
                      }
                      else{
                        $choice[1]["likes"] = 0;
                      }

                      if($RowF2["choice2_comment"] > 0){
                        $choice[1]["comments"] =  $RowF2["choice2_comment"];
                      }
                      else{
                        $choice[1]["comments"] = 0;
                      }

                      if($RowF2["choice2_share"] > 0){
                        $choice[1]["share"] =  $RowF2["choice2_share"];
                      }
                      else{
                        $choice[1]["share"] = 0;
                      }

                      $choice[1]["ismyLike"] = false;
                      foreach($myLike as $Islike){
                        if($Islike == '1'){
                          $choice[1]["ismyLike"] = true;
                        }
                      }

                      $choice[1]["ismyVote"] = false;
                      if($RowChoiceVote['selector'] == '1'){
                        $choice[1]["ismyVote"] = true;
                      }

                      ///////////////////////////////////////////////////////////////////////////////////

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

                        if($RowF2["choice3_like"] > 0){
                          $choice[2]["likes"] =  $RowF2["choice3_like"];
                        }
                        else{
                          $choice[2]["likes"] = 0;
                        }

                        if($RowF2["choice3_comment"] > 0){
                          $choice[2]["comments"] =  $RowF2["choice3_comment"];
                        }
                        else{
                          $choice[2]["comments"] = 0;
                        }

                        if($RowF2["choice3_share"] > 0){
                          $choice[2]["share"] =  $RowF2["choice3_share"];
                        }
                        else{
                          $choice[2]["share"] = 0;
                        }

                        $choice[2]["ismyLike"] = false;
                        foreach($myLike as $Islike){
                          if($Islike == '2'){
                            $choice[2]["ismyLike"] = true;
                          }
                        }

                        $choice[2]["ismyVote"] = false;
                        if($RowChoiceVote['selector'] == '2'){
                          $choice[2]["ismyVote"] = true;
                        }
                      }

                      $arrRowF1["date_start"] = $Row1["date_start"];
                      $arrRowF1["time_start"] = $Row1["time_start"];
                      $arrRowF1["date_end"] = $Row1["date_end"];
                      $arrRowF1["time_end"] = $Row1["time_end"];
                      // $answer = $Row1["anwser"];

                      if($Row1["anwser"] == 1){
                        $arrRowF1["answer"] = 0;
                      }
                      elseif($Row1["anwser"] == 2){
                        $arrRowF1["answer"] = 1;
                      }
                      elseif($Row1["anwser"] == 3){
                        $arrRowF1["answer"] = 2;
                      }

                      ////////////////////////////////////////////////////////////////////////////
                      $arrTime = array();
                      $origin = new DateTime();

                      $timeplus = "'".$Row1["date_start"]." ".$Row1["time_start"]."'";

                      if(date('Y-m-d H:i:s') >= $Row1["date_end"]." ".$Row1["time_end"]){
                        $arrRowF1["post_status"] = "close";
                        $arrRowF1["post_time_remain"] = "";
                      }
                      elseif(date('Y-m-d H:i:s') >= $Row1["date_start"]." ".$Row1["time_start"]){
                        $arrRowF1["post_status"] = "open";
                        $PreTime = new DateTime($Row1["date_end"]);

                        $interval = $origin->diff($PreTime);
                        $Ftime = $interval->format('%a days %h Hours %i Min');

                        $arrRowF1["post_time_remain"] = $Ftime;
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
                          $arrRowF1["post_status"] = "incoming";
                          $arrRowF1["post_time_remain"] = $minTime;
                        }
                        else{
                          $arrRowF1["post_status"] = "waiting";
                          $arrRowF1["post_time_remain"] = $Ftime;
                        }

                      }

                      // $arrRow["post_status"] = $arrTime;
                      ////////////////////////////////////////////////////////////////////////////
                    }

                    $arrRowF1["choice"] = $choice;
                    
                  }
                  else{
                    $arrRowF1["choice"] = null;
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

                  $arrRowF1["choice"] = $choice;
                  $arrRowF1["post_status"] = "normal";
                  $arrRowF1["post_time_remain"] = "";
                  $arrRowF1["createdate"] = $RowF1["CreateDate"];
                  $arrRowF1["createip"] = $RowF1["CreateIP"];
                  $arrRowF1["date_start"] = "";
                  $arrRowF1["time_start"] = "";
                  $arrRowF1["date_end"] = "";
                  $arrRowF1["time_end"] = "";
                }

                ////////////////////////////////////////////////////////////////////////////////////////////////

                $sqluser = $mysqli->prepare("SELECT * FROM user WHERE id = ? LIMIT 1");
                $sqluser->bind_param("i",$RowF1["user_id"]);
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
                  $arrDataUser["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$RowUser["avatar"];
                  $arrDataUser["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$RowUser["cover"];
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

                    $arrDataUser["title_th"] = $RowClass_User["title_th"];
                    $arrDataUser["title_en"] = $RowClass_User["title_en"];
                    $arrDataUser["point"] = $RowClass_User["point"];
                    $arrDataUser["picture"] = SITE_URL."uploads/user_level/images/".$RowClass_User["picture"];
                  }
                  else{
                    $arrDataUser["title_th"] = "";
                    $arrDataUser["title_en"] = "";
                    $arrDataUser['point'] = "";
                    $arrDataUser['picture'] = "";
                  }

                  $arrRowF1["publisher"] = $arrDataUser;
                }
                else{
                  $arrRowF1["publisher"] = null;
                }

                ///////////////////////////////////////////////////////////////////////////////////////////////
                $arrTypeTitle = explode(",",$RowF1["type_title"]);
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
                    $ArrTopic["name"] = $RowInterest["name_th"]."/".$RowInterest["name_en"];

                    $ArrInterest[] = $ArrTopic;
                  }
                }
                if($ArrInterest == null){
                  $arrRowF1["interest"] = null;
                }
                else{
                  $arrRowF1["interest"] = $ArrInterest;
                }
                

                ///////////////////////////////////////////////////////////////////////////////////////////////

                $sqlSelect = $mysqli->prepare("SELECT * FROM post_meta_user WHERE user_id = ? AND post_id = ? LIMIT 1");
                $sqlSelect->bind_param("ii",$myid,$RowF1["id"]);
                $sqlSelect->execute();
                $querySelect = $sqlSelect->get_result();

                if($querySelect->num_rows>0){
                  $RowSelect = $querySelect->fetch_assoc();
                  $arrRowF1["select_choice"] = $RowSelect["selector"];
                }
                else{
                  $arrRowF1["select_choice"] = '';
                }

                if($Row["user_tag"] != ''){
                  $exArrUser_tag = explode(",",$RowF1["user_tag"]);
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

                  $arrRowF1["tagusers"] = $Array_usertag;
                }
                else{
                  $arrRowF1["tagusers"] = null;
                }

                ///////////////////////////////////////////////////////////////////////////////////////////////

                if($Row["tag"] != ''){
                  $arrTag = explode(",",$RowF1["tag"]);
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

                  $arrRowF1["topic"] = $ArrTagGroup;
                }
                else{
                  $arrRowF1["topic"] = null;
                }


                ///////////////////////////////////////////////////////////////////////////////////////////////

                //$FaarRowF[] = $arrRowF1;
              }
            }
            else{
              $Result['data'] = null;
            }

            $Result['data'] = $arrRowF1;
          }
          else{
            $Result['data'] = null;
          }
        }
        else{
          $Result['message'] = 'TimeOut for Vote';
        }
      
    }
    else{
      $Result['message']  = 'Not Found Post';
    }
  }
  else{
    $Result['message']      = 'Can not cannect database';
  }
}
else{
  $Result['message']      = 'Missing Variable';
}

echo json_encode($Result);

?>