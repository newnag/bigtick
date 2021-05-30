<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0;
$Result['data']         =   null;


if(!empty($_POST['user_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];

      $sql = $mysqli->prepare("SELECT DISTINCT group_interest.group_id FROM user_interest 
                                JOIN group_interest ON group_interest.interest_id = user_interest.interest_id
                                WHERE user_id = ? ");
      $sql->bind_param("i", $myid);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';

        while($Row = $query->fetch_assoc()){
          $ArrRow[] = $Row;
        }

        foreach($ArrRow as $last){
          foreach($last as $lastID){
            $Finallast[] = $lastID;
          }
        }
        $in = '(' . implode(',', $Finallast) .')';

        $sqlF = $mysqli->prepare("SELECT * FROM group_user WHERE id IN ".$in." ");
        $sqlF->execute();
        $queryF  =   $sqlF->get_result();

        while($RowF = $queryF->fetch_assoc()){
          $ArrRowFinal["id"] = $RowF["id"];
          $ArrRowFinal["group_name"] = $RowF["group_name"];
          $ArrRowFinal["picture"] = SITE_URL."uploads/group/images/".$RowF["picture"];
          $ArrRowFinal["cover"] = SITE_URL."uploads/group/cover/".$RowF["cover_pic"];
          $ArrRowFinal["public_status"] = $RowF["public_status"];
          $ArrRowFinal["owner_id"] = $RowF["owner"];
          $ArrRowFinal["owner"] = $RowF["first_name"];
          $ArrRowFinal["createdate"] = $RowF["CreateDate"];
          $ArrRowFinal["createip"] = $RowF["CreateIP"];

          $Final_array[] = $ArrRowFinal;

          $Result['total_found'] += 1;
        }

        $Result['data'] = $Final_array; 
      }
      else{
        $Result['message'] = 'No Match Group';
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