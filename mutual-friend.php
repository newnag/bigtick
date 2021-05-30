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

      $sql = $mysqli->prepare("SELECT friend.friend_id,user.first_name
                                FROM friend 
                                JOIN user ON user.id = friend.friend_id
                                WHERE friend.user_id = ? ");
      $sql->bind_param("i",$myid);
      
      if($sql->execute()){
        $query  =   $sql->get_result();

        if($query->num_rows>0){
          $Result['status'] = 'Success';
          $Result['message'] = 'Get Data Success';
  
          while($Row = $query->fetch_assoc()){
            $arrRow = array();
  
            $arrRow["user_id"] = $Row["friend_id"];
            $arrRow["first_name"] = $Row["first_name"];

            //////////////////////////////////////////////////////////////////////////////////

            $sql1 = $mysqli->prepare("SELECT friend_id FROM friend WHERE user_id = ?");
            $sql1->bind_param("i",$arrRow["user_id"]);
            $sql1->execute();
            $query1 =  $sql1->get_result();

            $arrRow1 = array();
            $arrRow2 = array();
            while($Row1 = $query1->fetch_assoc()){
              
              $arrRow1[] = $Row1["friend_id"];
            }

            foreach($arrRow1 as $id_f2){
              array_push($arrRow2,$id_f2);
            }

            $in = '(' . implode(',', $arrRow2) .')';
           
            $sql2 = $mysqli->prepare("SELECT COUNT(user_id) as count_user FROM friend WHERE  user_id IN ".$in."  AND friend_id = ? ");
            $sql2->bind_param("i",$myid);
            $sql2->execute();
            $query2 =  $sql2->get_result();
            $Row2 = $query2->fetch_assoc();

            $arrRow["count_user"] = $Row2["count_user"];

            //////////////////////////////////////////////////////////////////////////////////////////
  
            $FaarRow[] = $arrRow;
  
            $Result['total_found'] += 1;
          }
          $Result['data'] = $FaarRow; 
        }
      }
      else{
        $Result['message'] = 'No Match Friend';
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