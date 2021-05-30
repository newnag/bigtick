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
$Result['data'] = null;


$mysqli = db_connect();
$ac_lang = get_headerLang();

if($mysqli){
  $user_id = $_POST['user_id'];
  $myid = $_POST['myid'];
  $group_id = $_POST['group_id'];


  $sql = $mysqli->prepare("SELECT * FROM noti WHERE my_id = ? AND Status <> 'Delete' ORDER BY CreateDate DESC ");
  $sql->bind_param("i",$myid,);
  $sql->execute();
  $query = $sql->get_result();

  if($query->num_rows > 0){
    $DataArr = array();

    while($row = $query->fetch_assoc()){

      if($row['user_id'] =! ""){
        $user = get_user($row['user_id']);
      }
      else{
        $user = null;
      }

      if($row['type'] == "vote"){
        $DataArr['msg'] = "ได้กดโหวตโพสต์ของคุณ";
        $DataArr['user'] = $user;
        $DataArr['group_name'] = "";
        $DataArr['group_pic'] = "";
        $DataArr['date'] = $row['CreateDate'];
      }
      elseif($row['type'] == "like"){
        $DataArr['msg'] = "ได้ไลค์โพสต์ของคุณ";
        $DataArr['user'] = $user;
        $DataArr['group_name'] = "";
        $DataArr['group_pic'] = "";
        $DataArr['date'] = $row['CreateDate'];
      }
      elseif($row['type'] == "comment"){
        $DataArr['msg'] = "ได้คอมเมนต์โพสของคุณ";
        $DataArr['user'] = $user;
        $DataArr['group_name'] = "";
        $DataArr['group_pic'] = "";
        $DataArr['date'] = $row['CreateDate'];
      }
      elseif($row['type'] == "follow"){
        $DataArr['msg'] = "ได้ขอติดตามคุณ";
        $DataArr['user'] = $user;
        $DataArr['group_name'] = "";
        $DataArr['group_pic'] = "";
        $DataArr['date'] = $row['CreateDate'];
      }
      elseif($row['type'] == "accept_follow"){
        $DataArr['msg'] = "ได้ตอบรับการติดตามแล้ว";
        $DataArr['user'] = $user;
        $DataArr['group_name'] = "";
        $DataArr['group_pic'] = "";
        $DataArr['date'] = $row['CreateDate'];
      }
      elseif($row['type'] == "accept_group"){
        $DataArr['msg'] = "ได้ยอมรับการขอเข้ากลุ่ม";
        $DataArr['user'] = null;

        $sql_group = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? AND Status <> 'Delete'");
        $sql_group->bind_param("i",$row['group_id'],);
        $sql_group->execute();
        $query_group = $sql_group->get_result();
        $row_group = $query_group->fetch_assoc();

        if($row_group['group_name'] == ""){
          $DataArr['group_name'] = "";
        }
        else{
          $DataArr['group_name'] = $row_group['group_name'];
        }
        
        if($row_group['picture'] == ""){
          $DataArr['group_pic'] = "";
        }
        else{
          $DataArr['group_pic'] = "http://bigtick.getdev.top/uploads/group/images/".$row_group['picture'];
        }

        $DataArr['date'] = $row['CreateDate'];
      }
      elseif($row['type'] == "invite_group"){
        $DataArr['msg'] = "ชวนคุณเข้ากลุ่มส่วนตัว";
        $DataArr['user'] = $user;
        $sql_group = $mysqli->prepare("SELECT * FROM group_user WHERE id = ? AND Status <> 'Delete'");
        $sql_group->bind_param("i",$row['group_id'],);
        $sql_group->execute();
        $query_group = $sql_group->get_result();
        $row_group = $query_group->fetch_assoc();

        if($row_group['group_name'] == ""){
          $DataArr['group_name'] = "";
        }
        else{
          $DataArr['group_name'] = $row_group['group_name'];
        }

        if($row_group['picture'] == ""){
          $DataArr['group_pic'] = "";
        }
        else{
          $DataArr['group_pic'] = "http://bigtick.getdev.top/uploads/group/images/".$row_group['picture'];
        }

        $DataArr['date'] = $row['CreateDate'];
      }

      ///////////////////////////////////////////////////////////////////////////



      $FaarRow[] = $DataArr;

    }



    $Result['status'] = 'Success';

    $Result['message'] = get_responseLang($ac_lang)[0];

    $Result['data'] = $FaarRow;

  }
  else{
    $Result['message'] = "ไม่มีการแจ้งเตือน";
  }
}

else{

  $Result['message'] = get_responseLang($ac_lang)[15];

}



echo json_encode($Result);



?>