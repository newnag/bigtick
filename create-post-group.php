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



if(!empty($_POST['action']) && !empty($_POST['user_id'])){

  $mysqli = db_connect();

  $ac_lang = get_headerLang();



  if($mysqli){

    $myid = $_POST['user_id'];

    $action = $_POST['action'];

    $createdate = date("Y-m-d H:i:s");

    $create_ip = $_SERVER["REMOTE_ADDR"];

    $pin_map = $_POST['location'];

    $title = $_POST['title'];

    $num_anwser = $_POST['num_answer'];

    $type_create_post = $_POST['post_style'];

    $type_group_other = $_POST['interest_other'];

    $tagF = $_POST['group_id'];

    $status = 'Enable';

    $group_post_status = "Yes";



    if($num_anwser == '1'){

      $type_create_post = 'horizon';

    }



    $group_vote = array();

    $tag = array();

    $tag_user = array();

    $type_group = array();

    

    foreach($_POST['type_group_vote'] as $Group_vote){

      $group_vote[] = $Group_vote;

    }

    foreach($_POST['user_tag'] as $User_tag){

      $tag_user[] = $User_tag;

    }

    foreach($_POST['interest'] as $Type_group){

      $type_group[] = $Type_group;

    }



    ////////////////////////////////////////////////////////

  

    $group_voteF = implode(',', $group_vote);

    $tag_userF = implode(',', $tag_user);

    $type_groupF = implode(',', $type_group);



    ////////////////////////////////////////////////////////



    if($action == "novote"){

      $Presql = $mysqli->prepare("SELECT id FROM post WHERE user_id = ? AND title = ? AND Status = 'Enable' LIMIT 1 ");

      $Presql->bind_param("is",$myid,$title);

      $Presql->execute();

      $Prequery = $Presql->get_result();



      if($Prequery->num_rows>0){

        $Result['message'] = get_responseLang($ac_lang)[18];

      }

      else{

        $sql = $mysqli->prepare("INSERT INTO post(user_id,type_vote,group_vote,tag,pin_map,user_tag,type_create_post,title,type_title,type_other,post_group_status,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $sql->bind_param("isssssssssssss",$myid,$action,$group_voteF,$tagF,$pin_map,$tag_userF,$type_create_post,$title,$type_groupF,$type_group_other,$group_post_status,$createdate,$create_ip,$status);

        if($sql->execute()){

          $picture = array();



          if(count($_FILES['choice_file']["name"]) > 0){

            for($i=0;$i<count($_FILES['choice_file']["name"]);$i++){

              $target_dir     =   "../uploads/posts/images";

              $avatar_name    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["choice_file"]["name"][$i]);

              $target_file    =   $target_dir .'/'.$avatar_name;

              $uploadOk       =   1;

              $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

              $maxsize = 200000000;



              $check = getimagesize($_FILES["choice_file"]["tmp_name"][$i]);

              $sizeFile = filesize($_FILES["choice_file"]["tmp_name"][$i]);



              // if($check !== false) {

              //   $uploadOk = 1;

              // } else {

              //   $uploadOk = 0;

              // }

  

              if($sizeFile > $maxsize){

                $uploadOk = 0; 

              }



              if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "heic" && $imageFileType != "heif" && $imageFileType != "hee" && $imageFileType != "raw" && $imageFileType != "pct" && $imageFileType != "pxr" && $imageFileType != "tif" && $imageFileType != "tiff" && $imageFileType != "wbmp" && $imageFileType != "gif" && $imageFileType != "mp4" ) {

                $uploadOk = 0;

              }



              if ($uploadOk == 1) {

                if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {

                    $picture[$i] = $avatar_name;

                }

              }

            }

          }

          else{

            for($i=0;$i<count($_POST['choice']);$i++){

              array_push($picture,$_POST['choice'][$i]);

            }

          }



          $postid = $sql->insert_id;

          $textImg = $_POST['text_img'];



          foreach($textImg as $textImgs){

            $actionPost[] = $textImgs;

          }  



          if(sizeof($actionPost) > 0){

            $sql2 = $mysqli->prepare("INSERT INTO post_meta_novote(post_id,choice_txtImg,Status) VALUES(?,?,?)");

            $sql2->bind_param("iis",$postid,$actionPost[0],$status);

  

            if($sql2->execute()){

              $Result['status'] = 'Success';

              $Result['message'] = get_responseLang($ac_lang)[2];

              $Result['data'] = $sql->insert_id;



              foreach($textImg as $txt){

                $sqlF2 = $mysqli->prepare("UPDATE action_post SET post_id = ? WHERE id = ? AND Status <> 'Delete' ");

                $sqlF2->bind_param("ii",$postid,$txt);

                $sqlF2->execute();

              }

            }

            else{

              $Result['message'] = get_responseLang($ac_lang)[19];

            } 

          }

          else{

            $sql2 = $mysqli->prepare("INSERT INTO post_meta_novote(post_id,picture,Status) VALUES(?,?,?)");

            $sql2->bind_param("iss",$postid,$picture[0],$status);

  

            if($sql2->execute()){

              $Result['status'] = 'Success';

              $Result['message'] = get_responseLang($ac_lang)[2];

              $Result['data'] = $sql->insert_id;

            }

            else{

              $Result['message'] = get_responseLang($ac_lang)[19];

            } 

          }

        }

        else{

          $Result['message'] = 'Can not Insert Data';

        }

      }

    }

    elseif($action == "quiz"){

      

      $Presql = $mysqli->prepare("SELECT id FROM post WHERE user_id = ? AND title = ? AND Status = 'Enable' LIMIT 1 ");

      $Presql->bind_param("is",$myid,$title);

      $Presql->execute();

      $Prequery = $Presql->get_result();



      if($Prequery->num_rows>0){

        $Result['message'] = get_responseLang($ac_lang)[18];

      }

      else{

        $sql = $mysqli->prepare("INSERT INTO post(user_id,type_vote,group_vote,tag,pin_map,user_tag,type_create_post,number_anwser,title,type_title,type_other,post_group_status,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $sql->bind_param("issssssisssssss",$myid,$action,$group_voteF,$tagF,$pin_map,$tag_userF,$type_create_post,$num_anwser,$title,$type_groupF,$type_group_other,$group_post_status,$createdate,$create_ip,$status);

        if($sql->execute()){

          $choice_file = array();

          $text_img_index = $_POST['text_img_index'];



          if(count($_FILES['choice_file']["name"]) > 0){

            for($i=0;$i<count($_FILES['choice_file']["name"]);$i++){

              $target_dir     =   "../uploads/posts/images";

              $avatar_name    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["choice_file"]["name"][$i]);

              $target_file    =   $target_dir .'/'.$avatar_name;

              $uploadOk       =   1;

              $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

              $maxsize = 200000000;



              $check = getimagesize($_FILES["choice_file"]["tmp_name"][$i]);

              $sizeFile = filesize($_FILES["choice_file"]["tmp_name"][$i]);



              // if($check !== false) {

              //   $uploadOk = 1;

              // } else {

              //   $uploadOk = 0;

              // }

  

              if($sizeFile > $maxsize){

                $uploadOk = 0; 

              }



              if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "heic" && $imageFileType != "heif" && $imageFileType != "hee" && $imageFileType != "raw" && $imageFileType != "pct" && $imageFileType != "pxr" && $imageFileType != "tif" && $imageFileType != "tiff" && $imageFileType != "wbmp" && $imageFileType != "gif" && $imageFileType != "mp4" ) {

                $uploadOk = 0;

              }



              if ($uploadOk == 1) {

                if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {

                    $choice_file[$i] = $avatar_name;

                }

              }



            }

          }

          else{

            for($i=0;$i<count($_POST['choice']);$i++){

              array_push($choice_file,$_POST['choice'][$i]);

            }

          }



          $choice_descript = array();



          for($i=0;$i<count($_POST['choice_title']);$i++){

            array_push($choice_descript,$_POST['choice_title'][$i]);

          }



          $anwser = strval($_POST['answer']);

          $date_start = $_POST['date_start'];

          $date_end = $_POST['date_end'];

          $time_start = $_POST['time_start'];

          $time_end = $_POST['time_end'];

          $id_insert = $sql->insert_id;

          $textImg = $_POST['text_img'];



          foreach($textImg as $textImgs){

            $actionPost[] = $textImgs;

          }  



          if(sizeof($actionPost) > 0){

            if($num_anwser == '2'){

              if(sizeof($text_img_index) == 2){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1_textImg,choice_2_textImg,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iiissssss",$id_insert,$actionPost[0],$actionPost[1],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(0,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1_textImg,choice_2,choice_2_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iissssssss",$id_insert,$actionPost[0],$choice_file[0],$choice_descript[0],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(1,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,choice_2_textImg,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("ississssss",$id_insert,$choice_file[0],$choice_descript[0],$actionPost[0],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }            

            

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = 'Insert Success';

                $Result['data'] = $id_insert;



                foreach($textImg as $txt){

                  $sqlF2 = $mysqli->prepare("UPDATE action_post SET post_id = ? WHERE id = ? AND Status <> 'Delete' ");

                  $sqlF2->bind_param("ii",$id_insert,$txt);

                  $sqlF2->execute();

                }

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

            elseif($num_anwser == '3'){

              if(sizeof($text_img_index) == 3){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1_textImg,choice_2_textImg,choice_3_textImg,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iiiissssss",$id_insert,$actionPost[0],$actionPost[1],$actionPost[2],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(0,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1_textImg,choice_2,choice_2_descript,choice_3,choice_3_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iissssssssss",$id_insert,$actionPost[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(1,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,choice_2_textImg,choice_3,choice_3_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("ississssssss",$id_insert,$choice_file[0],$choice_descript[0],$actionPost[0],$choice_file[1],$choice_descript[1],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(2,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,choice_2,choice_2_descript,choice_3_textImg,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("issssissssss",$id_insert,$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$actionPost[0],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(0,$text_img_index) && in_array(1,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1_textImg,choice_2_textImg,choice_3,choice_3_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iiissssssss",$id_insert,$actionPost[0],$actionPost[1],$choice_file[0],$choice_descript[0],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(0,$text_img_index) && in_array(2,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1_textImg,choice_2,choice_2_descript,choice_3_textImg,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iississssss",$id_insert,$actionPost[0],$choice_file[0],$choice_descript[0],$actionPost[1],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

              elseif(in_array(1,$text_img_index) && in_array(2,$text_img_index)){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,choice_2_textImg,choice_3_textImg,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("issiissssss",$id_insert,$choice_file[0],$choice_descript[0],$actionPost[0],$actionPost[1],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

              }

            

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = 'Insert Success';

                $Result['data'] = $id_insert;



                foreach($textImg as $txt){

                  $sqlF2 = $mysqli->prepare("UPDATE action_post SET post_id = ? WHERE id = ? AND Status <> 'Delete' ");

                  $sqlF2->bind_param("ii",$id_insert,$txt);

                  $sqlF2->execute();

                }

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

          }

          else{

            if($num_anwser == '2'){

              $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,choice_2,choice_2_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

              $sql1->bind_param("issssssssss",$id_insert,$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

            

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = 'Insert Success';

                $Result['data'] = $id_insert;

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

            elseif($num_anwser == '3'){

              $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,choice_2,choice_2_descript,choice_3,choice_3_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");

              $sql1->bind_param("issssssssssss",$id_insert,$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$choice_file[2],$choice_descript[2],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

            

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = 'Insert Success';

                $Result['data'] = $id_insert;

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

            elseif($num_anwser == '1'){

              $sql1 = $mysqli->prepare("INSERT INTO post_meta_quiz(post_id,choice_1,choice_1_descript,anwser,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?)");

              $sql1->bind_param("issssssss",$id_insert,$choice_file[0],$choice_descript[0],$anwser,$date_start,$time_start,$date_end,$time_end,$status);

            

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = 'Insert Success';

                $Result['data'] = $id_insert;

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

          }

        }

        else{

          $Result['message'] = get_responseLang($ac_lang)[19];

        }

      }

    }

    elseif($action == "vote"){

      $Presql = $mysqli->prepare("SELECT id FROM post WHERE user_id = ? AND title = ? AND Status = 'Enable' LIMIT 1 ");

      $Presql->bind_param("is",$myid,$title);

      $Presql->execute();

      $Prequery = $Presql->get_result();



      if($Prequery->num_rows>0){

        $Result['message'] = get_responseLang($ac_lang)[12];

      }

      else{

        if($num_anwser == '1'){

          $limit_vote = $_POST['limit_vote'];



          $sql = $mysqli->prepare("INSERT INTO post(user_id,type_vote,group_vote,tag,pin_map,user_tag,type_create_post,number_anwser,limit_number_vote,title,type_title,type_other,post_group_status,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

          $sql->bind_param("issssssiisssssss",$myid,$action,$group_voteF,$tagF,$pin_map,$tag_userF,$type_create_post,$num_anwser,$limit_vote,$title,$type_groupF,$type_group_other,$group_post_status,$createdate,$create_ip,$status);

          if($sql->execute()){

            $choice_file = array();   

  

            if(count($_FILES['choice_file']["name"]) > 0){

              for($i=0;$i<count($_FILES['choice_file']["name"]);$i++){

                $target_dir     =   "../uploads/posts/images";

                $avatar_name    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["choice_file"]["name"][$i]);

                $target_file    =   $target_dir .'/'.$avatar_name;

                $uploadOk       =   1;

                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

                $maxsize = 200000000;



                $check = getimagesize($_FILES["choice_file"]["tmp_name"][$i]);

                $sizeFile = filesize($_FILES["choice_file"]["tmp_name"][$i]);

  

                // if($check !== false) {

                //   $uploadOk = 1;

                // } else {

                //   $uploadOk = 0;

                // }

    

                if($sizeFile > $maxsize){

                  $uploadOk = 0; 

                }

  

                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "heic" && $imageFileType != "heif" && $imageFileType != "hee" && $imageFileType != "raw" && $imageFileType != "pct" && $imageFileType != "pxr" && $imageFileType != "tif" && $imageFileType != "tiff" && $imageFileType != "wbmp" && $imageFileType != "gif" && $imageFileType != "mp4" ) {

                  $uploadOk = 0;

                }

  

                if ($uploadOk == 1) {

                  if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {

                      $choice_file[$i] = $avatar_name;

                  }

                }

              }

            }

            else{

              for($i=0;$i<count($_POST['choice']);$i++){

                array_push($choice_file,$_POST['choice'][$i]);

              }

            }

  

            // $choice_descript = array();

  

            // for($i=0;$i<count($_POST['choice_title']);$i++){

            //   array_push($choice_descript,$_POST['choice_title'][$i]);

            // }

  

            $date_start = $_POST['date_start'];

            $date_end = $_POST['date_end'];

            $time_start = $_POST['time_start'];

            $time_end = $_POST['time_end'];

            $id_insert = $sql->insert_id;

            $textImg = $_POST['text_img'];

  

            //print_r($choice_file);

  

            if($textImg != ""){

              $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?) ");

              $sql1->bind_param("issssss",$id_insert,$textImg[0],$date_start,$time_start,$date_end,$time_end,$status);

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = get_responseLang($ac_lang)[2];

                $Result['data'] = $id_insert;



                $sqlF2 = $mysqli->prepare("UPDATE action_post SET post_id = ? WHERE id = ? AND Status <> 'Delete' ");

                $sqlF2->bind_param("ii",$id_insert,$textImg[0]);

                $sqlF2->execute();

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

            else{

              $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?) ");

              $sql1->bind_param("issssss",$id_insert,$choice_file[0],$date_start,$time_start,$date_end,$time_end,$status);

              if($sql1->execute()){

                $Result['status'] = 'Success';

                $Result['message'] = get_responseLang($ac_lang)[2];

                $Result['data'] = $id_insert;

              }

              else{

                $Result['message'] = get_responseLang($ac_lang)[19];

              }

            }

          }

          else{

            $Result['message'] = get_responseLang($ac_lang)[19];

          }

        }

        else{

          $sql = $mysqli->prepare("INSERT INTO post(user_id,type_vote,group_vote,tag,pin_map,user_tag,type_create_post,number_anwser,title,type_title,type_other,post_group_status,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

          $sql->bind_param("issssssisssssss",$myid,$action,$group_voteF,$tagF,$pin_map,$tag_userF,$type_create_post,$num_anwser,$title,$type_groupF,$type_group_other,$group_post_status,$createdate,$create_ip,$status);

          if($sql->execute()){

            $choice_file = array();

            $text_img_index = $_POST['text_img_index'];

  

            if(count($_FILES['choice_file']["name"]) > 0){

              for($i=0;$i<count($_FILES['choice_file']["name"]);$i++){

                $target_dir     =   "../uploads/posts/images";

                $avatar_name    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["choice_file"]["name"][$i]);

                $target_file    =   $target_dir .'/'.$avatar_name;

                $uploadOk       =   1;

                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

                $maxsize = 200000000;

  

                $check = getimagesize($_FILES["choice_file"]["tmp_name"][$i]);

                $sizeFile = filesize($_FILES["choice_file"]["tmp_name"][$i]);

  

                // if($check !== false) {

                //   $uploadOk = 1;

                // } else {

                //   $uploadOk = 0;

                // }

    

                if($sizeFile > $maxsize){

                  $uploadOk = 0; 

                }

  

                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "heic" && $imageFileType != "heif" && $imageFileType != "hee" && $imageFileType != "raw" && $imageFileType != "pct" && $imageFileType != "pxr" && $imageFileType != "tif" && $imageFileType != "tiff" && $imageFileType != "wbmp" && $imageFileType != "gif" && $imageFileType != "mp4" ) {

                  $uploadOk = 0;

                }

  

                if ($uploadOk == 1) {

                  if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {

                      $choice_file[$i] = $avatar_name;

                  }

                }

              }

            }

            else{

              // for($i=0;$i<count($_POST['choice']);$i++){

              //   array_push($choice_file,$_POST['choice'][$i]);

              // }

            }

  

            $choice_descript = array();

  

            for($i=0;$i<count($_POST['choice_title']);$i++){

              array_push($choice_descript,$_POST['choice_title'][$i]);

            }

  

            $date_start = $_POST['date_start'];

            $date_end = $_POST['date_end'];

            $time_start = $_POST['time_start'];

            $time_end = $_POST['time_end'];

            $id_insert = $sql->insert_id;

            $textImg = $_POST['text_img'];

  

            //print_r($choice_file);



            foreach($textImg as $textImgs){

              $actionPost[] = $textImgs;

            }



            if(sizeof($actionPost) > 0){

              $preChekAllIndex = array(0,1);



              if($num_anwser == '2'){

                if(sizeof($text_img_index) == 2){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("iiisssss",$id_insert,$actionPost[0],$actionPost[1],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(0,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2,choice_2_descript,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("iisssssss",$id_insert,$actionPost[0],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(1,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,choice_1_descript,choice_2_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("ississsss",$id_insert,$choice_file[0],$choice_descript[0],$actionPost[0],$date_start,$time_start,$date_end,$time_end,$status);

                }



                if($sql1->execute()){

                  $Result['status'] = 'Success';

                  $Result['message'] = get_responseLang($ac_lang)[2];

                  $Result['data'] = $id_insert;



                  foreach($textImg as $txt){

                    $sqlF2 = $mysqli->prepare("UPDATE action_post SET post_id = ? WHERE id = ? AND Status <> 'Delete' ");

                    $sqlF2->bind_param("ii",$id_insert,$txt);

                    $sqlF2->execute();

                  }

                }

                else{

                  $Result['message'] = get_responseLang($ac_lang)[19];

                }

              }

              elseif($num_anwser == '3'){

                if(sizeof($text_img_index) == 3){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2_textImg,choice_3_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("iiiisssss",$id_insert,$actionPost[0],$actionPost[1],$actionPost[2],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(0,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2,choice_2_descript,choice_3,choice_3_descript,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("iisssssssss",$id_insert,$actionPost[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(1,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,choice_1_descript,choice_2_textImg,choice_3,choice_3_descript,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("ississsssss",$id_insert,$choice_file[0],$choice_descript[0],$actionPost[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(2,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,choice_1_descript,choice_2,choice_2_descript,choice_3_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("issssisssss",$id_insert,$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$actionPost[0],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(0,$text_img_index) && in_array(1,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2_textImg,choice_3,choice_3_descript,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("iiisssssss",$id_insert,$actionPost[0],$actionPost[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(0,$text_img_index) && in_array(2,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2,choice_2_descript,choice_3_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("iississsss",$id_insert,$actionPost[0],$choice_file[0],$choice_descript[0],$actionPost[1],$date_start,$time_start,$date_end,$time_end,$status);

                }

                elseif(in_array(1,$text_img_index) && in_array(2,$text_img_index)){

                  $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,choice_1_descript,choice_2_textImg,choice_3_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                  $sql1->bind_param("issiisssss",$id_insert,$choice_file[0],$choice_descript[0],$actionPost[0],$actionPost[1],$date_start,$time_start,$date_end,$time_end,$status);

                }

                

                $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1_textImg,choice_2_textImg,choice_3_textImg,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("iiiisssss",$id_insert,$actionPost[0],$actionPost[1],$actionPost[2],$date_start,$time_start,$date_end,$time_end,$status);

                if($sql1->execute()){

                  $Result['status'] = 'Success';

                  $Result['message'] = get_responseLang($ac_lang)[2];

                  $Result['data'] = $id_insert;



                  foreach($textImg as $txt){

                    $sqlF2 = $mysqli->prepare("UPDATE action_post SET post_id = ? WHERE id = ? AND Status <> 'Delete' ");

                    $sqlF2->bind_param("ii",$id_insert,$txt);

                    $sqlF2->execute();

                  }

                }

                else{

                  $Result['message'] = get_responseLang($ac_lang)[19];

                }

              }

            }

            else{

              if($num_anwser == '2'){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,choice_1_descript,choice_2,choice_2_descript,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("isssssssss",$id_insert,$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$status);

                if($sql1->execute()){

                  $Result['status'] = 'Success';

                  $Result['message'] = get_responseLang($ac_lang)[2];

                  $Result['data'] = $id_insert;

                }

                else{

                  $Result['message'] = get_responseLang($ac_lang)[19];

                }

              }

              elseif($num_anwser == '3'){

                $sql1 = $mysqli->prepare("INSERT INTO post_meta(post_id,choice_1,choice_1_descript,choice_2,choice_2_descript,choice_3,choice_3_descript,date_start,time_start,date_end,time_end,Status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");

                $sql1->bind_param("isssssssssss",$id_insert,$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$choice_file[2],$choice_descript[2],$date_start,$time_start,$date_end,$time_end,$status);

                if($sql1->execute()){

                  $Result['status'] = 'Success';

                  $Result['message'] = get_responseLang($ac_lang)[2];

                  $Result['data'] = $id_insert;

                }

                else{

                  $Result['message'] = get_responseLang($ac_lang)[19];

                }

              }

            }

          }

          else{

            $Result['message'] = get_responseLang($ac_lang)[19];

          }

        }

      }

    }



  }

  else{

    $Result['message'] = get_responseLang($ac_lang)[15];

  }



}

else{

  $Result['message'] = get_responseLang($ac_lang)[14];;

}



echo json_encode($Result);



?>