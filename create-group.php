<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['user_id']) && !empty($_POST['group_name'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];
      $group_name = $_POST['group_name'];
      $public_status = $_POST['public_status'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $status = 'Enable';
      $picture = '';
      $cover = '';

      $presql = $mysqli->prepare("SELECT * FROM group_user WHERE group_name = ? LIMIT 1");
      $presql->bind_param("s", $group_name);
      $presql->execute();
      $prequery  =   $presql->get_result();

      if($prequery->num_rows>0){
        $Result['message'] = 'Exist Group';
      }
      else{
        
        //////////////////////////////////////////////////////////////////////////////////
        // รูปภาพประจำกลุ่ม
        //////////////////////////////////////////////////////////////////////////////////
        if($_FILES["picture"]["name"]){

          $target_dir1     =   "../uploads/group/images";
          $avatar_name1    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["picture"]["name"]);
          $target_file1    =   $target_dir1 .'/'.$avatar_name1;
          $uploadOk1       =   1;
          $imageFileType1 = strtolower(pathinfo($target_file1,PATHINFO_EXTENSION));

          $check1 = getimagesize($_FILES["picture"]["tmp_name"]);

          if($check1 !== false) {
            $uploadOk1 = 1;
          } else {
            $uploadOk1 = 0;
          }

          if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" ) {
            $uploadOk1 = 0;
          }

          if ($uploadOk1 == 1) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file1)) {
                $picture = $avatar_name1;
            }
          }
        }
        else{
          $picture = 'default.jpg';
        }

        //////////////////////////////////////////////////////////////////////////////////
        // รูปภาพหน้าปก
        //////////////////////////////////////////////////////////////////////////////////
        if($_FILES["cover"]["name"]){

          $target_dir     =   "../uploads/group/cover";
          $avatar_name    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["cover"]["name"]);
          $target_file    =   $target_dir .'/'.$avatar_name;
          $uploadOk       =   1;
          $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

          $check = getimagesize($_FILES["cover"]["tmp_name"]);

          if($check !== false) {
            $uploadOk = 1;
          } else {
            $uploadOk = 0;
          }

          if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $uploadOk = 0;
          }

          if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_file)) {
                $cover = $avatar_name;
            }
          }
        }
        else{
          $cover = 'default.jpg';
        }

        $sql = $mysqli->prepare("INSERT INTO group_user(group_name,picture,cover_pic,public_status,owner,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?,?)");
        $sql->bind_param("ssssssss", $group_name,$picture,$cover,$public_status,$myid,$createdate,$create_ip,$status);
        $sql->execute();

        $num = $sql->insert_id;

        // $test = 0;
        foreach($_POST['interest_id'] as $interest_id){
          $sql1    =   $mysqli->prepare("INSERT INTO group_interest (group_id , interest_id , CreateDate , CreateIP , Status) VALUES (?,?,?,?,?) ");
          $sql1->bind_param("iisss", $num , $interest_id , $createdate, $create_ip , $status);
          $sql1->execute();
          $test+=1;
        }

        // $Result['test'] = $test;

        ///////////////////////////////////////////////////////////////////////////////

        $request_status = "Approve";

        $sqlMember = $mysqli->prepare("INSERT INTO group_member(group_id,user_id,request_status,approve_by,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?)");
        $sqlMember->bind_param("iisisss", $num,$myid,$request_status,$myid,$createdate,$create_ip,$status);
        $sqlMember->execute();

        ///////////////////////////////////////////////////////////////////////////////
  
        $Result['status'] = 'Success';
        $Result['message'] = 'Insert Success';
        $Result['data'] = $num;
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