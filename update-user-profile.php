<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];
      $createdate = date("Y-m-d H:i:s");
      $create_ip = $_SERVER["REMOTE_ADDR"];
      $error_user = false;

      $presql = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND status <> 'Delete' ");
      $presql->bind_param("i",$myid);
      $presql->execute();
      $prequery = $presql->get_result();
      $PreRow = $prequery->fetch_assoc();

      $username = $_POST['username'];

      $presql1 = $mysqli->prepare("SELECT * FROM user WHERE id <> ? AND status <> 'Delete' ");
      $presql1->bind_param("i",$myid);
      $presql1->execute();
      $prequery1 = $presql1->get_result();

      while($RowPre1 = $prequery1->fetch_assoc()){
        if($username == $RowPre1['username']){
          $error_user = true;
        }
      }

      if($username == ''){
        $username = $PreRow['username'];
      }

      $occupation_id = $_POST["occupation_id"];
      $occupation_address = $_POST["occupation_address"];

      if($occupation_id == ''){
        $occupation_id = $PreRow['occupation_id'];
      }
      if($occupation_address == ''){
        $occupation_address = $PreRow['work_address'];
      }

      $address = $_POST['address'];
      $country_id = $_POST['country_id'];
      $country_other = $_POST['country_other'];
      $province_id = $_POST['province_id'];
      $province_other = $_POST['province_other'];

      if($address == ''){
        $address = $PreRow['address'];
      }
      if($country_id == ''){
        $country_id = $PreRow['country_id'];
      }
      if($country_other == ''){
        $country_other = $PreRow['country_other'];
      }
      if($province_id == ''){
        $province_id = $PreRow['province_id'];
      }
      if($province_other == ''){
        $province_other = $PreRow['province_other'];
      }
      
      $telephone = $_POST['telephone'];
      $facebook = $_POST['facebook'];
      $line = $_POST['line'];

      if($telephone == ''){
        $telephone = $PreRow['telephone'];
      }
      if($facebook == ''){
        $facebook = $PreRow['facebook'];
      }
      if($line == ''){
        $line = $PreRow['line'];
      }

      if($_FILES["avatar"]["name"]){
        $target_dir1     =   "../uploads/user/images";
        $avatar_name1    =   md5('Ymds').'-'.$myid.'-'.date("Ymd-His").'-'.basename($_FILES["avatar"]["name"]);
        $target_file1    =   $target_dir1 .'/'.$avatar_name1;
        $uploadOk1       =   1;
        $imageFileType1 = strtolower(pathinfo($target_file1,PATHINFO_EXTENSION));

        $check1 = getimagesize($_FILES["avatar"]["tmp_name"]);

        if($check1 !== false) {
          $uploadOk1 = 1;
        } else {
          $uploadOk1 = 0;
        }

        if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" ) {
          $uploadOk1 = 0;
        }

        if ($uploadOk1 == 1) {
          if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file1)) {
              $picture = $avatar_name1;
          }
        }
      }
      else{
        $picture = $PreRow['avatar'];
      }

      if($_FILES["cover"]["name"]){
        $target_dir     =   "../uploads/user/cover";
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
        $cover = $PreRow['cover'];
      }

      $first_name = $_POST['first_name'];
      $last_name = $_POST['last_name'];

      if($first_name == ''){
        $first_name = $PreRow['first_name'];
      }
      if($last_name == ''){
        $last_name = $PreRow['last_name'];
      }

      if(!$error_user){
        $sql = $mysqli->prepare("UPDATE user SET first_name=?,last_name=?,avatar=?,cover=?,telephone=?,username=?,facebook=?,line=?,address=?,country_id=?,country_other=?,province_id=?,province_other=?,occupation_id=?,work_address=?,updatedate=?,update_ip=? WHERE id=? ");
        $sql->bind_param("sssssssssisisisssi",$first_name,$last_name,$picture,$cover,$telephone,$username,$facebook,$line,$address,$country_id,$country_other,$province_id,$province_other,$occupation_id,$occupation_address,$createdate,$create_ip,$myid);

        if($sql->execute()){
          $Result['status'] = 'Success';
          $Result['message'] = 'Update Data Success';
        }
        else{
          $Result['message'] = 'No Match User';
        }
      }
      else{
        $Result['message'] = 'มี username นี้อยู่ในระบบ';
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