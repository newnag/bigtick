<?php 
    /** 
     * upload-chat image
     * version: 1.0
     * Author : keng <songkritr@gmail.com> 
    */

    header('Content-Type: application/json; charset=utf-8');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST");

    $AllowPublic = true; 
    require('../administrator/include/config.inc.php');
    require('../administrator/include/function.inc.php');
    
    $status  = "Error";
    $message = "";
    $data = null;
    print_r($_POST['title']);

    if( isset($_FILES['image']) && !empty($_FILES['image']['name']) ){

        $result = uploadFile($_FILES['image']);

        $status = $result['status'] ;
        $message = $result['message'];
        $data = $result['data'];
    }
    else {
        $message = "ส่งไฟล์มาด้วยจ้า key image";
    }

    $res = array(
        "status" => $status,
        "message" => $message,
        "data" => $data
    );
    echo json_encode($res);

    function uploadFile($file) {
        
        //define('URL_UPLOADS',$_SERVER["DOCUMENT_ROOT"].'/bigtick/api/images/');
        define('URL_UPLOADS','../uploads/chats/images/');
        $site_upload = 'https://bigtick.getdev.top/uploads/chats/images/';    
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_temp = $file['tmp_name'];
        $file_type = $file['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $file_upload_name = "img-".date("Y-m-d-his").".".$file_ext;
        $file_upload =  URL_UPLOADS.$file_upload_name;
        $file_path = "uploads/chats/images/".$file_upload_name;

        $extensions= array("jpeg","jpg","png","gif");
        if(in_array($file_ext,$extensions) == false){
            $errors = "Please choose a JPG,PNG,GIF file.";
        }
        else {
            $file_mb = 5;
            $file_max_size = 1048576 * $file_mb;
            if($file_size > $file_max_size ){
                $errors = "File size must be excately ".$file_mb." MB";
            }
        }
        
        $status = "Error";
        $message = "";
        $data = null;

        if(empty($errors) == true){
            if(move_uploaded_file($file_temp,$file_upload)) {
                $status = "Success";
                $message = "Upload file success.";
                $data = array(
                    "filepath" => SITE_URL.$file_path,
                    "filename" => $file_upload_name
                ); 
            }
            else {
                $message = "Upload file fail.";
            }
        }else{
            $message = $errors;
        }

        $res = array(
            "status" => $status,
            "message" => $message,
            "data" => $data
        );
        
        return $res;
    }
?>