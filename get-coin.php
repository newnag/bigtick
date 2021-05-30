<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']      =   null;

if(!empty($_POST['user_id'])){
  $mysqli     =   db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $user_id = $_POST['user_id'];
    $create_date = date('Y-m-d H:i:s');
    $createip = $_SERVER["REMOTE_ADDR"];
    $status = 'Enable';
    $totalcoin = 0;

    /////////////////////////////////////////////////////////////////////////////////////////
    // ส่วนเพิ่มแต้มหลังจากโพสปิดแล้ว

    $presql = $mysqli->prepare("SELECT * FROM post_meta_user WHERE user_id = ? AND Status <> 'Delete' ");
    $presql->bind_param("i",$user_id);
    $presql->execute();
    $prequery = $presql->get_result();

    if($prequery->num_rows > 0){
      while($prerow = $prequery->fetch_assoc()){
        $presql1 = $mysqli->prepare("SELECT * FROM post WHERE id = ? AND Status <> 'Delete' ");
        $presql1->bind_param("i",$prerow['post_id']);
        $presql1->execute();
        $prequery1 = $presql1->get_result();
        $prerow1 = $prequery1->fetch_assoc();

        if($prerow1["type_vote"] == 'vote'){
          $presql2 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? AND Status <> 'Delete' ");
          $presql2->bind_param("i",$prerow1['id']);
          $presql2->execute();
          $prequery2 = $presql2->get_result();
          $prerow2 = $prequery2->fetch_assoc();

          if(date('Y-m-d H:i:s') > date($prerow2["date_end"]." ".$prerow2["time_end"])){
            if($prerow1["number_anwser"] == '1'){
              if($prerow2["choice_1_result"] >= $prerow1["limit_number_vote"]){
                $presql3 = $mysqli->prepare("SELECT * FROM history_coin_receive WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
                $presql3->bind_param("ii",$prerow1['id'],$user_id);
                $presql3->execute();
                $prequery3 = $presql3->get_result();
      
                if($prequery3->num_rows == 0){
                  $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                  $sql->bind_param("i",$user_id);
                  $sql->execute();
                  $query = $sql->get_result();
                  $Row = $query->fetch_assoc();
      
                  $coin = $Row["point"] + 1;
      
                  $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                  $sql1->bind_param("issi",$coin,$create_date,$createip,$user_id);
                  if($sql1->execute()){
                    $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                    $sql2->bind_param("iisss",$user_id,$prerow1['id'],$create_date,$createip,$status);
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
                $presql3->bind_param("ii",$prerow1['id'],$user_id);
                $presql3->execute();
                $prequery3 = $presql3->get_result();
      
                if($prequery3->num_rows == 0){
                  $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                  $sql->bind_param("i",$user_id);
                  $sql->execute();
                  $query = $sql->get_result();
                  $Row = $query->fetch_assoc();
      
                  $coin = $Row["point"] + 1;
      
                  $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                  $sql1->bind_param("issi",$coin,$create_date,$createip,$user_id);
                  if($sql1->execute()){
                    $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                    $sql2->bind_param("iisss",$user_id,$prerow1['id'],$create_date,$createip,$status);
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
                $presql3->bind_param("ii",$prerow1['id'],$user_id);
                $presql3->execute();
                $prequery3 = $presql3->get_result();
      
                if($prequery3->num_rows == 0){
                  $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                  $sql->bind_param("i",$user_id);
                  $sql->execute();
                  $query = $sql->get_result();
                  $Row = $query->fetch_assoc();
      
                  $coin = $Row["point"] + 1;
      
                  $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                  $sql1->bind_param("issi",$coin,$create_date,$createip,$user_id);
                  if($sql1->execute()){
                    $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                    $sql2->bind_param("iisss",$user_id,$prerow1['id'],$create_date,$createip,$status);
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
          $prerow2 = $prequery2->fetch_assoc();

          if(date('Y-m-d H:i:s') > date($prerow2["date_end"]." ".$prerow2["time_end"])){
            $anwser = $prerow2["anwser"];

            if($prerow["selector"] == $anwser){
              $presql3 = $mysqli->prepare("SELECT * FROM history_coin_receive WHERE post_id = ? AND user_id = ? AND Status <> 'Delete' ");
              $presql3->bind_param("ii",$prerow1['id'],$user_id);
              $presql3->execute();
              $prequery3 = $presql3->get_result();
    
              if($prequery3->num_rows == 0){
                $sql = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
                $sql->bind_param("i",$user_id);
                $sql->execute();
                $query = $sql->get_result();
                $Row = $query->fetch_assoc();
    
                $coin = $Row["point"] + 1;
    
                $sql1 = $mysqli->prepare("UPDATE class_user SET point = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? ");
                $sql1->bind_param("issi",$coin,$create_date,$createip,$user_id);
                if($sql1->execute()){
                  $sql2 = $mysqli->prepare("INSERT INTO history_coin_receive(user_id,post_id,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?)");
                  $sql2->bind_param("iisss",$user_id,$prerow1['id'],$create_date,$createip,$status);
                  if($sql2->execute()){
                    $totalcoin ++;
                  }
                }
              }
            }
          }
        }
      }
    }

    if($totalcoin != 0){
      $Result_coin['receive_coin']  =  true;
      $Result_coin['coin']  =  $totalcoin;
    }
    else{
      $Result_coin['receive_coin']  =  false;
      $Result_coin['coin']  =  0;
    }

    $arrCoin[] = $Result_coin;

    /////////////////////////////////////////////////////////////////////////////////////////
    // ส่วนเช็คอัพระดับสมาชิก

    $sql_class = $mysqli->prepare("SELECT * FROM user_level WHERE (amount*10) <= (SELECT point FROM class_user WHERE user_id = ? AND Status <> 'Delete' Limit 1) AND Status <> 'Delete' ORDER BY id DESC LIMIT 1");
    $sql_class->bind_param("i",$user_id);
    $sql_class->execute();
    $query_class = $sql_class->get_result();

    if($query_class->num_rows > 0){
      $Row_class = $query_class->fetch_assoc();

      $sqlMember = $mysqli->prepare("SELECT * FROM class_user WHERE user_id = ? AND Status <> 'Delete' ");
      $sqlMember->bind_param("i",$user_id);
      $sqlMember->execute();
      $queryMember = $sqlMember->get_result();
      $RowMember = $queryMember->fetch_assoc();

      if($RowMember['class_name'] != $Row_class['title_en']){
        $ResultClass['old_class'] = $RowMember['class_name'];

        $sql_up_member = $mysqli->prepare("UPDATE class_user SET class_name = ?, UpdateDate = ?, UpdateIP = ? WHERE user_id = ? AND Status <> 'Delete' ");
        $sql_up_member->bind_param("sssi",$Row_class['title_en'],$create_date,$createip,$user_id);
        if($sql_up_member->execute()){
          $ResultClass['upgrade_member']  =  true;
          $ResultClass['new_class'] = $Row_class['title_en'];
        }
      }
      else{
        $ResultClass['old_class'] = "";
        $ResultClass['upgrade_member'] = false;
        $ResultClass['new_class'] = "";
      }
    }
    else{
      $ResultClass['old_class'] = "";
      $ResultClass['upgrade_member'] = false;
      $ResultClass['new_class'] = "";
    }

    $arrUp[] = $ResultClass;

    /////////////////////////////////////////////////////////////////////////////////////////

    $sql = $mysqli->prepare("SELECT a.point,b.picture,b.title_en,b.title_th FROM class_user as a JOIN user_level as b ON b.title_en = a.class_name WHERE a.user_id = ? AND a.Status <> 'Delete' ");
    $sql->bind_param("i",$user_id);
    $sql->execute();
    $query  =  $sql->get_result();

    if($query->num_rows > 0){
      $Row = $query->fetch_assoc();

      $getUser = get_user($user_id);

      $arrData["id"] = $getUser["id"];
      $arrData["facebook_id"] = $getUser["facebook_id"];
      $arrData["gmail_id"] = $getUser["gmail_id"];
      $arrData["apple_id"] = $getUser["apple_id"];
      $arrData["access_token"] = $getUser["access_token"];
      $arrData["avatar"] = "https://bigtick.getdev.top/uploads/user/images/".$getUser["avatar"];
      $arrData["cover"] = "https://bigtick.getdev.top/uploads/user/cover/".$getUser["cover"];
      $arrData["username"] = $getUser["username"];
      $arrData["first_name"] = $getUser["first_name"];
      $arrData["last_name"] = $getUser["last_name"];
      $arrData["gender"] = $getUser["gender"];
      $arrData["gender_other"] = $getUser["gender_other"];
      $arrData["birthdate"] = $getUser["birthdate"];
      $arrData["telephone"] = $getUser["telephone"];
      $arrData["email"] = $getUser["email"];
      $arrData["facebook"] = $getUser["facebook"];
      $arrData["line"] = $getUser["line"];
      $arrData["address"] = $getUser["address"];
      $arrData["country_id"] = $getUser["country_id"];
      $arrData["country_other"] = $getUser["country_other"];
      $arrData["province_id"] = $getUser["province_id"];
      $arrData["province_other"] = $getUser["province_other"];
      $arrData["occupation_id"] = $getUser["occupation_id"];
      $arrData["work_address"] = $getUser["work_address"];
      $arrData["about_me"] = $getUser["about_me"];
      $arrData["status"] = $getUser["status"];
      $arrData["createdate"] = $getUser["createdate"];
      $arrData["create_ip"] = $getUser["create_ip"];
      $arrData["updatedate"] = $getUser["updatedate"];
      $arrData["update_ip"] = $getUser["update_ip"];
      $arrData["lastlogin"] = $getUser["lastlogin"];
      $arrData["lastlogin_ip"] = $getUser["lastlogin_ip"];

      switch($ac_lang){
        case "en":
          $arrData["member_title"] = $Row["title_en"];
          break;
        case "th":
          $arrData["member_title"] = $Row["title_th"];
          break;

        default:
          $arrData["member_title"] = $Row["title_th"];
      }
      $arrData["member_coin"] = $Row["point"];
      $arrData["member_icon"] = SITE_URL."uploads/user_level/images/".$Row["picture"];

      $mypoint = $Row["point"];

      $sql1 = $mysqli->prepare("SELECT * FROM user_level WHERE (amount*10) > ".$mypoint." AND status <> 'Delete' ORDER BY amount LIMIT 1 ");
      $sql1->execute();
      $query1  =  $sql1->get_result();

      if($query1 ->num_rows > 0){
        $Row1 = $query1->fetch_assoc();

        $sum_point = ($Row1['amount']*10) - $mypoint;
  
        $arrData['point_to_upgrade'] = $sum_point;
        switch($ac_lang){
          case "en":
            $arrData['class_to_upgrade'] = $Row1['title_en'];
            break;
          case "th":
            $arrData['class_to_upgrade'] = $Row1['title_th'];
            break;
  
          default:
          $arrData['class_to_upgrade'] = $Row1['title_th'];
        }
      }
      else{
        $arrData['point_to_upgrade'] = "";
        $arrData['class_to_upgrade'] = "";
        $arrData['class_to_upgrade'] = "";
      }

      //////////////////////////////////////////////////////////

      $arrReward = array();

      $sql2 = $mysqli->prepare("SELECT * FROM reward WHERE status <> 'Delete' ORDER BY amount ");
      $sql2->execute();
      $query2  =  $sql2->get_result();

      if($query2 ->num_rows > 0){
        while($Row2 = $query2->fetch_assoc()){
          $arrPre_Reward['id'] = $Row2['id'];
          switch($ac_lang){
            case "en":
              $arrPre_Reward['title_reward'] = $Row2['title_en'];
              break;
            case "th":
              $arrPre_Reward['title_reward'] = $Row2['title_th'];
              break;
    
            default:
            $arrPre_Reward['title_reward'] = $Row2['title_th'];
          }
          $arrPre_Reward['amount'] = $Row2['amount'];
          $arrPre_Reward['picture'] = SITE_URL."uploads/reward/images/".$Row2['picture'];

          $arrReward[] = $arrPre_Reward;
        }

        $arrData['Reward'] = $arrReward;
      }
      else{
        $arrData['Reward'] = null;
      }

      $arrData["upgrade"] = $arrUp;

      //////////////////////////////////////////////////////////

      $arrData["get_coin"] = $arrCoin;

      $Result['status'] = 'Success';
      $Result['message'] = 'Get Data Success';
      $Result['data'] = $arrData;
    }
    else{
      $Result['message'] = 'Not Found User';
    }
  }
  else{
    $Result['message']      = 'Can not cannect database';
  }   

}else{
  $Result['message']  = 'Missing Variable';
}

echo json_encode($Result);

?>