<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['data']         =   null;


if(!empty($_POST['post_id']) && !empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $post_id = $_POST['post_id'];
      $myid = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT post.id,post.user_id,post.type_vote,post.group_vote,post.pin_map,post.type_create_post,post.title,user.first_name,class_user.class_name
                                FROM post 
                                JOIN user ON user.id = post.user_id
                                JOIN class_user ON class_user.user_id = post.user_id
                                WHERE post.id = ? AND post.user_id = ? AND post.Status <> 'Delete' ");
      $sql->bind_param("ii",$post_id,$myid);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';

        while($Row = $query->fetch_assoc()){
          $arrRow = array();

          $arrRow["id"] = $Row["id"];
          $arrRow["title"] = $Row["title"];
          $arrRow["type_vote"] = $Row["type_vote"];
          $arrRow["pin_map"] = $Row["pin_map"];
          $arrRow["type_create_post"] = $Row["type_create_post"];
          $arrRow["first_name"] = $Row["first_name"];
          $arrRow["class_name"] = $Row["class_name"];

          if($arrRow["type_vote"] == 'vote'){
            $sql1 = $mysqli->prepare("SELECT * FROM post_meta WHERE post_id = ? ");
            $sql1->bind_param("i",$arrRow["id"]);
            $sql1->execute();
            $query1 = $sql1->get_result();

            if($query1->num_rows>0){
              while($Row1 = $query1->fetch_assoc()){
                $exArr1 =  explode(".",$Row1["choice_1"]);
                if($exArr1[1]){
                  $arrRow["choice_1"] = SITE_URL."uploads/post/images/".$Row1["choice_1"];
                }
                else{
                  $arrRow["choice_1"] = $Row1["choice_1"];
                }
                
                $arrRow["choice_1_descript"] = $Row1["choice_1_descript"];
                $arrRow["choice_1_result"] = $Row1["choice_1_result"];

                $exArr2 =  explode(".",$Row1["choice_2"]);
                if($exArr2[1]){
                  $arrRow["choice_2"] = SITE_URL."uploads/post/images/".$Row1["choice_1"];
                }
                else{
                  $arrRow["choice_2"] = $Row1["choice_2"];
                }

                $arrRow["choice_2_descript"] = $Row1["choice_2_descript"];
                $arrRow["choice_2_result"] = $Row1["choice_2_result"];

                $exArr3 =  explode(".",$Row1["choice_3"]);
                if($exArr3[1]){
                  $arrRow["choice_3"] = SITE_URL."uploads/post/images/".$Row1["choice_1"];
                }
                else{
                  $arrRow["choice_3"] = $Row1["choice_3"];
                }

                $arrRow["choice_3_descript"] = $Row1["choice_3_descript"];
                $arrRow["choice_3_result"] = $Row1["choice_3_result"];
                $arrRow["date_start"] = $Row1["date_start"];
                $arrRow["time_start"] = $Row1["time_start"];
                $arrRow["date_end"] = $Row1["date_end"];
                $arrRow["time_end"] = $Row1["time_end"];
              }
            }
            else{
              $Result['message'] = 'Post not found';
            }
          }
          elseif($arrRow["type_vote"] == 'quiz'){
            $sql1 = $mysqli->prepare("SELECT * FROM post_meta_quiz WHERE post_id = ? ");
            $sql1->bind_param("i",$arrRow["id"]);
            $sql1->execute();
            $query1 = $sql1->get_result();

            if($query1->num_rows>0){
              while($Row1 = $query1->fetch_assoc()){
                $exArr1 =  explode(".",$Row1["choice_1"]);
                if($exArr1[1]){
                  $arrRow["choice_1"] = SITE_URL."uploads/post/images/".$Row1["choice_1"];
                }
                else{
                  $arrRow["choice_1"] = $Row1["choice_1"];
                }
                
                $arrRow["choice_1_descript"] = $Row1["choice_1_descript"];
                $arrRow["choice_1_result"] = $Row1["choice_1_result"];

                $exArr2 =  explode(".",$Row1["choice_2"]);
                if($exArr2[1]){
                  $arrRow["choice_2"] = SITE_URL."uploads/post/images/".$Row1["choice_1"];
                }
                else{
                  $arrRow["choice_2"] = $Row1["choice_2"];
                }

                $arrRow["choice_2_descript"] = $Row1["choice_2_descript"];
                $arrRow["choice_2_result"] = $Row1["choice_2_result"];

                $exArr3 =  explode(".",$Row1["choice_3"]);
                if($exArr3[1]){
                  $arrRow["choice_3"] = SITE_URL."uploads/post/images/".$Row1["choice_1"];
                }
                else{
                  $arrRow["choice_3"] = $Row1["choice_3"];
                }

                $arrRow["choice_3_descript"] = $Row1["choice_3_descript"];
                $arrRow["choice_3_result"] = $Row1["choice_3_result"];
                $arrRow["date_start"] = $Row1["date_start"];
                $arrRow["time_start"] = $Row1["time_start"];
                $arrRow["date_end"] = $Row1["date_end"];
                $arrRow["time_end"] = $Row1["time_end"];
              }
            }
            else{
              $Result['message'] = 'Post not found';
            }
          }
          else{
            $arrRow["choice_1"] = '';
            $arrRow["choice_1_descript"] = '';
            $arrRow["choice_1_result"] = '';
            $arrRow["choice_2"] = '';
            $arrRow["choice_2_descript"] = '';
            $arrRow["choice_2_result"] = '';
            $arrRow["choice_3"] = '';
            $arrRow["choice_3_descript"] = '';
            $arrRow["choice_3_result"] = '';
            $arrRow["date_start"] = '';
            $arrRow["time_start"] = '';
            $arrRow["date_end"] = '';
            $arrRow["time_end"] = '';
            $arrRow["createdate"] = $Row["CreateDate"];
            $arrRow["createip"] = $Row["CreateIP"];
          }

          $sqlSelect = $mysqli->prepare("SELECT * FROM post_meta_user WHERE user_id = ? AND post_id = ? LIMIT 1");
          $sqlSelect->bind_param("ii",$myid,$Row["id"]);
          $sqlSelect->execute();
          $querySelect = $sqlSelect->get_result();

          if($querySelect->num_rows>0){
            $RowSelect = $querySelect->fetch_assoc();
            $arrRow["select_choice"] = $RowSelect["selector"];
          }
          else{
            $arrRow["select_choice"] = '';
          }
          
          $FaarRow[] = $arrRow;

        }
        $Result['data'] = $FaarRow; 
      }
      else{
        $Result['message'] = 'No Match Post';
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