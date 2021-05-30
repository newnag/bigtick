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
$Result['total_found']  =   0;    

if(!empty($_POST['user_id'])){
  $mysqli = db_connect();
  $ac_lang = get_headerLang();

  if($mysqli){
    $myid = $_POST['user_id'];
    $who_follow = $_POST['who_follow'];
    $who_see_profile = $_POST['who_see_profile'];
    $who_see_post = $_POST['who_see_post'];
    $who_see_act = $_POST['who_see_act'];
    $auto_follow = $_POST['auto_follow'];
    $alert_push = $_POST['alert_push'];
    $sound_vib = $_POST['sound_vib'];
    $alert_vote_mypost = $_POST['alert_vote_mypost'];
    $alert_new_post_friend = $_POST['alert_new_post_friend'];
    $alert_invite_group = $_POST['alert_invite_group'];
    $alert_like_mypost = $_POST['alert_like_mypost'];
    $alert_comment_mypost = $_POST['alert_comment_mypost'];
    $alert_share_mypost = $_POST['alert_share_mypost'];
    $alert_follow_me = $_POST['alert_follow_me'];
    $alert_accept_follow = $_POST['alert_accept_follow'];
    $lang = $_POST['lang'];
    $createdate = date('Y-m-d H:i:s');
    $create_ip = $_SERVER["REMOTE_ADDR"];

    ///////////////////////////////////////////////////////////////////////
    $presql = $mysqli->prepare("SELECT * FROM user_setting WHERE user_id = ? ");
    $presql->bind_param("i",$myid);
    $presql->execute();
    $query = $presql->get_result();
    $Row = $query->fetch_assoc();

    $error = true;

    if($who_follow == ''){
      $who_follow = $Row['who_follow'];
    }
    if($who_see_profile == ''){
      $who_see_profile = $Row['who_see_profile'];
    }
    if($who_see_post == ''){
      $who_see_post = $Row['who_see_post'];
    }
    if($who_see_act == ''){
      $who_see_act = $Row['who_see_act'];
    }
    if($auto_follow == ''){
      $auto_follow = $Row['auto_follow'];
    }
    if($alert_push == ''){
      $alert_push = $Row['alert_push'];
    }
    if($sound_vib == ''){
      $sound_vib = $Row['sound_vib'];
    }
    if($alert_vote_mypost == ''){
      $alert_vote_mypost = $Row['alert_vote_mypost'];
    }
    if($alert_new_post_friend == ''){
      $alert_new_post_friend = $Row['alert_new_post_friend'];
    }
    if($alert_invite_group == ''){
      $alert_invite_group = $Row['alert_invite_group'];
    }
    if($alert_like_mypost == ''){
      $alert_like_mypost = $Row['alert_like_mypost'];
    }
    if($alert_comment_mypost == ''){
      $alert_comment_mypost = $Row['alert_comment_mypost'];
    }
    if($alert_share_mypost == ''){
      $alert_share_mypost = $Row['alert_share_mypost'];
    }
    if($alert_follow_me == ''){
      $alert_follow_me = $Row['alert_follow_me'];
    }
    if($alert_accept_follow == ''){
      $alert_accept_follow = $Row['alert_accept_follow'];
    }
    if($lang == ''){
      $lang = $Row['lang'];
    }

    $sql = $mysqli->prepare("UPDATE user_setting SET who_follow=?,who_see_profile=?,who_see_post=?,who_see_act=?,auto_follow=?,alert_push=?,sound_vib=?,alert_vote_mypost=?,alert_new_post_friend=?,alert_invite_group=?,alert_like_mypost=?,alert_comment_mypost=?,alert_share_mypost=?,alert_follow_me=?,alert_accept_follow=?,lang=?,update_date=?,update_ip=? WHERE user_id = ? ");
    $sql->bind_param("iiiissssssssssssssi",$who_follow,$who_see_profile,$who_see_post,$who_see_act,$auto_follow,$alert_push,$sound_vib,$alert_vote_mypost,$alert_new_post_friend,$alert_invite_group,$alert_like_mypost,$alert_comment_mypost,$alert_share_mypost,$alert_follow_me,$alert_accept_follow,$lang,$createdate,$create_ip,$myid);
    if($sql->execute()){
      $error = false;
    }
    
    if(!$error){
      $Result['status'] = 'Success';
      $Result['message'] = get_responseLang($ac_lang)[1];
    }
    else{
      $Result['message'] = get_responseLang($ac_lang)[19];
    }
  }
  else{
    $Result['message'] = get_responseLang($ac_lang)[15];
  }
}
else{
  $Result['message'] = get_responseLang($ac_lang)[14];
}


echo json_encode($Result);

?>