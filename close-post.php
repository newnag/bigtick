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
  $mysqli     =   db_connect();

  if($mysqli){
    /////////////////////////////////////////////////////////////////////////////////////////
    // ส่วนเพิ่มแต้มหลังจากโพสปิดแล้ว

    $my_id = $_POST['user_id'];
    $post_id = $_POST['post_id'];
    $create_date = date('Y-m-d H:i:s');
    $createip = $_SERVER["REMOTE_ADDR"];
    $status = 'Enable';
    $totalcoin = 0;

    $presql = $mysqli->prepare("SELECT * FROM post_meta_user WHERE user_id = ? AND post_id = ? AND Status <> 'Delete' ");
    $presql->bind_param("ii",$my_id,$post_id);
    $presql->execute();
    $prequery = $presql->get_result();
    $prerow = $prequery->fetch_assoc();
  
    $presql1 = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
    $presql1->bind_param("i",$post_id);
    $presql1->execute();
    $prequery1 = $presql1->get_result();
    if($prequery1->num_rows > 0){
      $prerow1 = $prequery1->fetch_assoc();

      if($prerow1["type_vote"] == 'vote'){
        $presql2 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
        $presql2->bind_param("i",$post_id);
        $presql2->execute();
        $prequery2 = $presql2->get_result();
        $prerow2 = $prequery2->fetch_assoc();
  
        if(date('Y-m-d H:i:s') > date($prerow2["date_end"]." ".$prerow2["time_end"])){
          if($prerow1["number_anwser"] == '1'){
            if($prerow2["choice_1_result"] >= $prerow1["limit_number_vote"]){
              $presql3 = $mysqli->prepare("SELECT * FROM history_coin_receive WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
              $presql3->bind_param("ii",$prerow1['id'],$my_id);
              $presql3->execute();
              $prequery3 = $presql3->get_result();
    
              if($prequery3->num_rows == 0){
                $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                $sql->bind_param("i",$my_id);
                $sql->execute();
                $query = $sql->get_result();
                $Row = $query->fetch_assoc();
    
                $coin = $Row["point"] + 1;
    
                $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                $sql1->bind_param("issi",$coin,$create_date,$createip,$my_id);
                if($sql1->execute()){
                  $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                  $sql2->bind_param("iisss",$my_id,$prerow1['id'],$create_date,$createip,$status);
                  if($sql2->execute()){
                    $totalcoin ++;
                  }
                }
              }
            }
          }
          elseif($prerow1["number_anwser"] == '2'){
            $maximum = max($prerow2["choice_1_result"],$prerow2["choice_2_result"]);
  
            if($prerow2["choice_1_result"] == $maximum){
              $max_choice = '0';
            }
            elseif($prerow2["choice_2_result"] == $maximum){
              $max_choice = '1';
            }
  
            if($max_choice == $prerow["selector"]){
              $presql3 = $mysqli->prepare("SELECT * FROM history_coin_receive WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
              $presql3->bind_param("ii",$prerow1['id'],$my_id);
              $presql3->execute();
              $prequery3 = $presql3->get_result();
    
              if($prequery3->num_rows == 0){
                $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                $sql->bind_param("i",$my_id);
                $sql->execute();
                $query = $sql->get_result();
                $Row = $query->fetch_assoc();
    
                $coin = $Row["point"] + 1;
    
                $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                $sql1->bind_param("issi",$coin,$create_date,$createip,$my_id);
                if($sql1->execute()){
                  $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                  $sql2->bind_param("iisss",$my_id,$prerow1['id'],$create_date,$createip,$status);
                  if($sql2->execute()){
                    $totalcoin ++;
                  }
                }
              }
            }
          }
          elseif($prerow1["number_anwser"] == '3'){
            $maximum = max($prerow2["choice_1_result"],$prerow2["choice_2_result"],$prerow2["choice_3_result"]);
  
            if($prerow2["choice_1_result"] == $maximum){
              $max_choice = '0';
            }
            elseif($prerow2["choice_2_result"] == $maximum){
              $max_choice = '1';
            }
            elseif($prerow2["choice_3_result"] == $maximum){
              $max_choice = '2';
            }
  
            if($max_choice == $prerow["selector"]){
              $presql3 = $mysqli->prepare("SELECT * FROM history_coin_receive WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
              $presql3->bind_param("ii",$prerow1['id'],$my_id);
              $presql3->execute();
              $prequery3 = $presql3->get_result();
    
              if($prequery3->num_rows == 0){
                $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                $sql->bind_param("i",$my_id);
                $sql->execute();
                $query = $sql->get_result();
                $Row = $query->fetch_assoc();
    
                $coin = $Row["point"] + 1;
    
                $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                $sql1->bind_param("issi",$coin,$create_date,$createip,$my_id);
                if($sql1->execute()){
                  $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                  $sql2->bind_param("iisss",$my_id,$prerow1['id'],$create_date,$createip,$status);
                  if($sql2->execute()){
                    $totalcoin ++;
                  }
                }
              }
            }
          }
        }
      }
      elseif($prerow1["type_vote"] == 'quiz'){
        $presql2 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? AND Status <> 'Delete' ");
        $presql2->bind_param("i",$prerow1['id']);
        $presql2->execute();
        $prequery2 = $presql2->get_result();
        if($prequery2->num_rows > 0){
          $prerow2 = $prequery2->fetch_assoc();
  
          if(date('Y-m-d H:i:s') > date($prerow2["date_end"]." ".$prerow2["time_end"])){
            $anwser = $prerow2["anwser"];
    
            if($prerow["selector"] == $anwser){
              $presql3 = $mysqli->prepare("SELECT * FROM history_coin_receive WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
              $presql3->bind_param("ii",$prerow1['id'],$my_id);
              $presql3->execute();
              $prequery3 = $presql3->get_result();
    
              if($prequery3->num_rows == 0){
                $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                $sql->bind_param("i",$my_id);
                $sql->execute();
                $query = $sql->get_result();
                $Row = $query->fetch_assoc();
    
                $coin = $Row["point"] + 1;
    
                $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                $sql1->bind_param("issi",$coin,$create_date,$createip,$my_id);
                if($sql1->execute()){
                  $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                  $sql2->bind_param("iisss",$my_id,$prerow1['id'],$create_date,$createip,$status);
                  if($sql2->execute()){
                    $totalcoin ++;
                  }
                  else{
                    $Result['message']  = 'Cant add coin';
                  }
                }
                else{
                  $Result['message']  = 'Cant add coin';
                }
              }
              else{
                $Result['message']  = 'You ever got coin';
              }
            }
          }
        }
        else{
          $Result['message']  = 'Not Found Post Choice';
        }
      }
  
      if($totalcoin != 0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Update Data Success';
        $Result['Coin']  =  'You have receive coin';
      }
    }
    else{
      $Result['message']  = 'Not Found Post';
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    // ส่วนเช็คอัพระดับสมาชิก

    $sql_class = $mysqli->prepare("SELECT * FROM user_level WHERE Status <> 'Delete' ");
    $sql_class->execute();
    $query_class = $sql_class->get_result();
    while($Row_class = $query_class->fetch_assoc()){
      $sql_member = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
      $sql_member->bind_param("i",$my_id);
      $sql_member->execute();
      $query_member = $sql_member->get_result();
      $Row_member = $query_member->fetch_assoc();

      if($Row_member['point'] >= $Row_class['amount'] && $Row_member['class_name'] != $Row_class['title_en']){
        $sql_up_member = $mysqli->prepare("UPDATE class_user SET class_name = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? AND Status <> 'Delete' ");
        $sql_up_member->bind_param("sssi",$Row_class['title_en'],$create_date,$createip,$my_id);
        if($sql_up_member->execute()){
          $Result['Upgrade_Member']  =  'You have Upgrade Class Member';
        }
      }
    }

    /////////////////////////////////////////////////////////////////////////////////////////



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