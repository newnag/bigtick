<?php

header("Content-Type: application/json");



$AllowPublic = true; 

require('../administrator/include/config.inc.php');

require('../administrator/include/function.inc.php');



$Result['status']       =   'Error';

$Result['message']      =   '';

$Result['data']         =   null;



if(!empty($_POST['user_id']) && !empty($_POST['text']) && !empty($_POST['bg_color'])){

  $mysqli     =   db_connect();

  $ac_lang = get_headerLang();



  if($mysqli){

    $user_id = $_POST['user_id'];

    $text = $_POST['text'];

    $bg_color = $_POST['bg_color'];

    $txt_color = $_POST['txt_color'];

    $choice = intval($_POST['choice']);

    $createdate = date("Y-m-d H:i:s");

    $createIP = $_SERVER["REMOTE_ADDR"];

    $status = 'Enable';



    ////////////////////////////////////////////////////////////////////////////



    $tmp_name = md5(date("Y-m-d H:i:s"))."actionPost";

    $path = "../uploads/action_post/images/".$tmp_name.".png";

    $file_name = $tmp_name.".png";


    $height = 300;
    $width = 1300;

    $text_bbox = ImageTTFBBox(20, 0, "Sarabun.ttf", $text);
    $image_centerx = $width / 2;
    $image_centery = ($height / 2);
    $text_x = $image_centerx - round(($text_bbox[4]/2))+70;
    $text_y = $image_centery;

    $line = wordwrap($text,100,"\n",true);

    list($br, $bg, $bb) = sscanf($bg_color,'#%02x%02x%02x');
    list($tr, $tg, $tb) = sscanf($txt_color,'#%02x%02x%02x');

    $images = ImageCreate($width,$height);
    $color = imagecolorallocate($images,$br,$bg,$bb);
    $text_color = imagecolorallocate($images,$tr,$tg,$tb);
    ImageTTFText($images, 20, 0, $text_x, $text_y, $text_color, "TH_Niramit.ttf",$line);
    // ImageTTFText($images, 20, 0, 3, $image_centery, $text_color, "TH_Niramit.ttf",$line);
    ImagePng($images,$path);
    ImageDestroy($images);

    ////////////////////////////////////////////////////////////////////////////



    $sql = $mysqli->prepare("INSERT INTO action_post(user_id,picture,choice,text,bg_color,text_color,CreateDate,CreateIP,Status) VALUES(?,?,?,?,?,?,?,?,?)");

    $sql->bind_param("isissssss",$user_id,$file_name,$choice,$text,$bg_color,$txt_color,$createdate,$createIP,$status);

    if($sql->execute()){

      $Result['status'] = 'Success';

      $Result['message'] = 'เพิ่มข้อมูลเรียบร้อย';



      $arrData["id"] = $sql->insert_id;

      $arrData["choice"] = $choice;

      $arrData["picture"] = SITE_URL."uploads/action_post/images/".$file_name;

    }

    else{

      $Result['message'] = "ไม่สามารถเพิ่มรูปภาพเข้าระบบได้";

    }

    

    ////////////////////////////////////////////////////////////////////////////



    $Result['data'] = $arrData;

  }

  else{

    $Result['message']      = get_responseLang($ac_lang)[15];

  }    

}else{

  $Result['message']      = get_responseLang($ac_lang)[14];

}



echo json_encode($Result);



?>