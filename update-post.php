<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
// $Result['data']         =   null;


if(!empty($_POST['post_id']) && $_POST['user_id']){
    $mysqli     =   db_connect();

    if($mysqli){
      $post_id = $_POST['post_id'];
      $myid = $_POST['user_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];

      $presql = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND user_id = ? ");
      $presql->bind_param("ii",$post_id,$myid);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows > 0){
        $PreRow = $prequery->fetch_assoc();

        $group_vote = array();
        $tag = array();
        $tag_user = array();
        $type_group = array();

        if(!empty($_POST['location'])){
          $pin_map = $_POST['location'];
        }
        else{
          $pin_map = $PreRow["pin_map"];
        }

        if(!empty($_POST['title'])){
          $title = $_POST['title'];
        }
        else{
          $title = $PreRow["title"];
        }

        if(!empty($_POST['num_answer'])){
          $num_anwser = $_POST['num_answer'];
        }
        else{
          $num_anwser = $PreRow["number_anwser"];
        }

        if(!empty($_POST['post_style'])){
          $type_create_post = $_POST['post_style'];
        }
        else{
          $type_create_post = $PreRow["type_create_post"];
        }

        if(!empty($_POST['interest_other'])){
          $type_group_other = $_POST['interest_other'];
        }
        else{
          $type_group_other = $PreRow["type_other"];
        }

        foreach($_POST['type_group_vote'] as $Group_vote){
          $group_vote[] = $Group_vote;
        }
        foreach($_POST['group'] as $Tag){
          $tag[] = $Tag;
        }
        foreach($_POST['user_tag'] as $User_tag){
          $tag_user[] = $User_tag;
        }
        foreach($_POST['interest'] as $Type_group){
          $type_group[] = $Type_group;
        }

        ////////////////////////////////////////////////////////

        $group_voteF = implode(',', $group_vote);
        $tagF = implode(',', $tag);
        $tag_userF = implode(',', $tag_user);
        $type_groupF = implode(',', $type_group);

        ////////////////////////////////////////////////////////

        $sql = $mysqli->prepare("UPDATE post SET group_vote=?,tag=?,pin_map=?,user_tag=?,type_create_post=?,number_anwser=?,title=?,type_title=?,type_other=?,UpdateDate=?,UpdateIP=? WHERE id=? AND user_id=?");
        $sql->bind_param("sssssisssssii",$group_voteF,$tagF,$pin_map,$tag_userF,$type_create_post,$num_anwser,$title,$type_groupF,$type_group_other,$createdate,$create_ip,$post_id,$myid);
        if($sql->execute()){

          $date_start = $_POST['date_start'];
          $date_end = $_POST['date_end'];
          $time_start = $_POST['time_start'];
          $time_end = $_POST['time_end'];

          if($PreRow["type_vote"] == 'vote'){
            $presql2 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? LIMIT 1");
            $presql2->bind_param("i",$PreRow["id"]);
            $presql2->execute();
            $prequery2  =   $presql2->get_result();
            $PreRow2 = $prequery2->fetch_assoc();

            ////////////////////////////////////////////////////////////////////////////////
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
  
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "mp4" ) {
                  $uploadOk = 0;
                }
  
                if ($uploadOk == 1) {
                  if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {
                      $choice_file[$i] = $avatar_name;
                  }
                }
              }
            }
            elseif(count($_POST['choice']) > 0){
              for($i=0;$i<count($_POST['choice']);$i++){
                array_push($choice_file,$_POST['choice'][$i]);
              }
            }
            else{
              $choice_file[0] = $PreRow2["choice_1"];
              $choice_file[1] = $PreRow2["choice_2"];
              $choice_file[2] = $PreRow2["choice_3"];
            }
            ////////////////////////////////////////////////////////////////////////////////
            $choice_descript = array();

            if(sizeof($_POST['choice_title']) > 0){
              for($i=0;$i<count($_POST['choice_title']);$i++){
                array_push($choice_descript,$_POST['choice_title'][$i]);
              }
            }
            else{
              $choice_descript[0] = $PreRow2["choice_1_descript"];
              $choice_descript[1] = $PreRow2["choice_2_descript"];
              $choice_descript[2] = $PreRow2["choice_3_descript"];
            }
            ////////////////////////////////////////////////////////////////////////////////

            if(sizeof($_POST['text_img']) > 0){
              foreach($_POST['text_img'] as $arrTextImg){
                $textImg[] = $arrTextImg;
              }
            }
            else{
              $textImg[0] = $PreRow['choice_1_textImg'];
              $textImg[1] = $PreRow['choice_2_textImg'];
              $textImg[2] = $PreRow['choice_3_textImg'];
            }

            $text_img_index = $_POST['text_img_index'];

            ///////////////////////////////////////////////////////////////////////////////

            if($PreRow["number_anwser"] == 2){
              if(sizeof($text_img_index) > 0){
                if(sizeof($text_img_index) == 2){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2_textImg=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssi",$textImg[0],$textImg[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2=?,choice_2_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssi",$textImg[0],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(1,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_textImg=?,choice_1=?,choice_1_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssi",$textImg[0],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
              }
              else{
                $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1=?,choice_1_descript=?,choice_2=?,choice_2_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                $sql2->bind_param("ssssssssi",$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
              }

              $sql2->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
            }
            elseif($PreRow["number_anwser"] == 3){
              if(sizeof($text_img_index) > 0){
                if(sizeof($text_img_index) == 3){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2_textImg=?,choice_3_textImg=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iiissssi",$textImg[0],$textImg[1],$textImg[2],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index) && in_array(1,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2_textImg=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssssi",$textImg[0],$textImg[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index) && in_array(2,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_3_textImg=?,choice_2=?,choice_2_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssssi",$textImg[0],$textImg[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(1,$text_img_index) && in_array(2,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_textImg=?,choice_3_textImg=?,choice_1=?,choice_1_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssssi",$textImg[0],$textImg[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2=?,choice_2_descript=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(1,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_textImg=?,choice_1=?,choice_1_descript=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(2,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_3_textImg=?,choice_1=?,choice_1_descript=?,choice_2=?,choice_2_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
              }
              else{
                $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1=?,choice_1_descript=?,choice_2=?,choice_2_descript=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                $sql2->bind_param("ssssssssssi",$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$choice_file[2],$choice_descript[2],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
              }

              $sql2->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
            }
            elseif($PreRow["number_anwser"] == 1){
              if(sizeof($_POST['text_img']) > 0){
                $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                $sql2->bind_param("issssi",$textImg[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
              }
              else{
                $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1=?,choice_1_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                $sql2->bind_param("ssssssi",$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
              }

              $sql2->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
            }
            else{
              $Result['message'] = 'Error Something';
            }
            
          }
          elseif($PreRow["type_vote"] == 'quiz'){
            $presql2 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? LIMIT 1");
            $presql2->bind_param("i",$PreRow["id"]);
            $presql2->execute();
            $prequery2  =   $presql2->get_result();
            $PreRow2 = $prequery2->fetch_assoc();

            ////////////////////////////////////////////////////////////////////////////////
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
  
                if($check !== false) {
                  $uploadOk = 1;
                } else {
                  $uploadOk = 0;
                }
    
                if($sizeFile > $maxsize){
                  $uploadOk = 0; 
                }
  
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "mp4" ) {
                  $uploadOk = 0;
                }
  
                if ($uploadOk == 1) {
                  if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {
                      $choice_file[$i] = $avatar_name;
                  }
                }
              }
            }
            elseif(count($_POST['choice']) > 0){
              for($i=0;$i<count($_POST['choice']);$i++){
                array_push($choice_file,$_POST['choice'][$i]);
              }
            }
            else{
              $choice_file[0] = $PreRow2["choice_1"];
              $choice_file[1] = $PreRow2["choice_2"];
              $choice_file[2] = $PreRow2["choice_3"];
            }
            ////////////////////////////////////////////////////////////////////////////////
            $choice_descript = array();

            if($_POST['choice_title'] > 0){
              for($i=0;$i<count($_POST['choice_title']);$i++){
                array_push($choice_descript,$_POST['choice_title'][$i]);
              }
            }
            else{
              $choice_descript[0] = $PreRow2["choice_1_descript"];
              $choice_descript[1] = $PreRow2["choice_2_descript"];
              $choice_descript[2] = $PreRow2["choice_3_descript"];
            }
            ////////////////////////////////////////////////////////////////////////////////

            if(sizeof($_POST['text_img']) > 0){
              foreach($_POST['text_img'] as $arrTextImg){
                $textImg[] = $arrTextImg;
              }
            }
            else{
              $textImg[0] = $PreRow2['choice_1_textImg'];
              $textImg[1] = $PreRow2['choice_2_textImg'];
              $textImg[2] = $PreRow2['choice_3_textImg'];
            }

            $text_img_index = $_POST['text_img_index'];

            ////////////////////////////////////////////////////////////////////////////////

            if($_POST['anwser'] != ''){
              $anwser = $_POST['anwser'];
            }
            else{
              $anwser = $PreRow2['anwser'];
            }

            ////////////////////////////////////////////////////////////////////////////////

            if($PreRow["number_anwser"] == 2){
              if(sizeof($text_img_index) > 0){
                if(sizeof($text_img_index) == 2){
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_1_textImg=?,choice_2_textImg=?,anwser=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssi",$textImg[0],$textImg[1],$anwser,$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_1_textImg=?,choice_2=?,choice_2_descript=?,anwser=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("isssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$anwser,$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(1,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_2_textImg=?,choice_1=?,choice_1_descript=?,anwser=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("isssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$anwser,$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
              }
              else{
                $sql2 = $mysqli->prepare("UPDATE post_meta_quiz SET choice_1=?,choice_1_descript=?,choice_2=?,choice_2_descript=?,anwser=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                $sql2->bind_param("ssssssssi",$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$anwser,$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
              }

              $sql2->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
            }
            elseif($PreRow["number_anwser"] == 3){
              if(sizeof($text_img_index) > 0){
                if(sizeof($text_img_index) == 3){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2_textImg=?,choice_3_textImg=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iiissssi",$textImg[0],$textImg[1],$textImg[2],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index) && in_array(1,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2_textImg=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssssi",$textImg[0],$textImg[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index) && in_array(2,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_3_textImg=?,choice_2=?,choice_2_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssssi",$textImg[0],$textImg[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(1,$text_img_index) && in_array(2,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_textImg=?,choice_3_textImg=?,choice_1=?,choice_1_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("iissssssi",$textImg[0],$textImg[1],$choice_file[0],$choice_descript[0],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(0,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1_textImg=?,choice_2=?,choice_2_descript=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(1,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_2_textImg=?,choice_1=?,choice_1_descript=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
                elseif(in_array(2,$text_img_index)){
                  $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_3_textImg=?,choice_1=?,choice_1_descript=?,choice_2=?,choice_2_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                  $sql2->bind_param("issssssssi",$textImg[0],$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
                }
              }
              else{
                $sql2 = $mysqli->prepare("UPDATE post_meta SET choice_1=?,choice_1_descript=?,choice_2=?,choice_2_descript=?,choice_3=?,choice_3_descript=?,date_start=?,time_start=?,date_end=?,time_end=? WHERE id=?");
                $sql2->bind_param("ssssssssssi",$choice_file[0],$choice_descript[0],$choice_file[1],$choice_descript[1],$choice_file[2],$choice_descript[2],$date_start,$time_start,$date_end,$time_end,$PreRow2["id"]);
              }
              
              $sql2->execute();
  
              $Result['status'] = 'Success';
              $Result['message'] = 'Update Success';
            }
            else{
              $Result['message'] = 'Error Something';
            }
          }
          elseif($PreRow["type_vote"] == 'novote'){
            $presql2 = $mysqli->prepare("SELECT * FROM post_meta_novote WHERE post_id = ? LIMIT 1");
            $presql2->bind_param("i",$PreRow["id"]);
            $presql2->execute();
            $prequery2  =   $presql2->get_result();
            $PreRow2 = $prequery2->fetch_assoc();

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
  
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "mp4" ) {
                  $uploadOk = 0;
                }
  
                if ($uploadOk == 1) {
                  if (move_uploaded_file($_FILES["choice_file"]["tmp_name"][$i], $target_file)) {
                      $choice_file[$i] = $avatar_name;
                  }
                }
              }
            }
            elseif(count($_POST['choice']) > 0){
              for($i=0;$i<count($_POST['choice']);$i++){
                array_push($choice_file,$_POST['choice'][$i]);
              }
            }
            else{
              $choice_file[0] = $PreRow2["picture"];
            }
            ////////////////////////////////////////////////////////////////////////////////

            if(sizeof($_POST['text_img']) > 0){
              foreach($_POST['text_img'] as $arrTextImg){
                $textImg[] = $arrTextImg;
              }
            }
            else{
              $textImg[0] = $PreRow['choice_txtImg'];
            }

            if(sizeof($_POST['text_img']) > 0){
              $sql2 = $mysqli->prepare("UPDATE post_meta_novote SET choice_txtImg=? WHERE id=?");
              $sql2->bind_param("ii",$textImg[0],$PreRow2["id"]);
            }
            else{
              $sql2 = $mysqli->prepare("UPDATE post_meta_novote SET picture=? WHERE id=?");
              $sql2->bind_param("si",$choice_file[0],$PreRow2["id"]);
            }

            ////////////////////////////////////////////////////////////////////////////////
            
            $sql2->execute();

            $Result['status'] = 'Success';
            $Result['message'] = 'Update Success';
          }
        }
        else{
          $Result['message'] = 'Not Found Post';
        }
      }
      else{
        $Result['message'] = 'Not Found Post';
      }
    }
    else{
      $Result['message']      = 'Can not cannect database';
    }    
}
else{
  $Result['message'] = 'Missing Variable';
}

echo json_encode($Result);

?>