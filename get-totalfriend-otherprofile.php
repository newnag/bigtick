<?php
header("Content-Type: application/json");

$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']      =   0;
$Result['data']         =   null;


if(!empty($_POST['user_id']) && !empty($_POST['profile_id'])){
    $mysqli     =   db_connect();

    if($mysqli){
      $myid = $_POST['user_id'];
      $profile_id = $_POST['profile_id'];

      $sql = $mysqli->prepare("SELECT b.*
                                FROM friend
                                JOIN user as b ON b.id = friend.friend_id
                                WHERE friend.user_id = ? AND friend.friend_id <> ?  AND friend.Status = 'Enable' ");
      $sql->bind_param("ii", $myid,$profile_id);
      $sql->execute();
      $query  =   $sql->get_result();

      if($query->num_rows>0){
        $Result['status'] = 'Success';
        $Result['message'] = 'Get Data Success';

        while($Row = $query->fetch_assoc()){
          $FaarRow[] = $Row;

          $Result['total_found'] += 1;
        }
        $Result['data'] = $FaarRow; 
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