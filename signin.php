<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 0");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
header("Content-Type: application/json");


$AllowPublic = true; 
require('../administrator/include/config.inc.php');
require('../administrator/include/function.inc.php');

$Result['status']       =   'Error';
$Result['message']      =   '';
$Result['total_found']   =   0;    
$Result['data']         =   null;

$URL_IMAGE = SITE_URL."uploads/user/images/";

if(!empty($_POST['signin_type']) && ( ($_POST['signin_type']=='username') || ($_POST['signin_type']=='facebook') || ($_POST['signin_type']=='gmail') || ($_POST['signin_type']=='apple')) ){

    $mysqli     =   db_connect();

    if($mysqli){

                    $signin_type    =   secure_input($_POST['signin_type']);


                    if($signin_type == "username"){

    
                                $username   =   secure_input($_POST['username']);
                                $password   =   secure_input($_POST['password']);

                                $sql    =   $mysqli->prepare("SELECT * FROM user WHERE username = ? OR email = ? OR telephone = ?  LIMIT 1");
                                $sql->bind_param("sss", $username ,$username ,$username);
                                $sql->execute();
                                $query  =   $sql->get_result();

                                if($query->num_rows>0){

                                        $Row    =   $query->fetch_assoc();

                                        if (password_verify($password,$Row["password"])) { 

                                            $access_token           =   password_hash($Row['id'].'-'.date("YmdHis"),PASSWORD_DEFAULT);
                                            //$Row['access_token']    =   $access_token;
                                            $Row['avatar']          =   $URL_IMAGE.$Row['avatar'];
                                            $Result['status']       =   'Success';
                                            $Result['message']      =   'Signin Success';
                                            $Result['total_found']  =   $query->num_rows;
                                            $Result['data']         =   $Row;


                                            //$sql    =   $mysqli->prepare("UPDATE user SET access_token = ? WHERE id = ?");
                                            //$sql->bind_param("si", $Row['access_token'] ,$Row['id']);
                                            //$sql->execute();
                                        

                                        }else{

                                            $Result['message']      = 'Signin Failed';

                                        }   

                                }else{

                                        $Result['message']      = 'Signin Failed';

                                }     



                    }else{


                                $signin_id   =   secure_input($_POST['signin_id']);

                                if($signin_type == "facebook"){

                                        $sql    =   $mysqli->prepare("SELECT * FROM user WHERE facebook_id = ? LIMIT 1");

                                }else if($signin_type == "gmail"){

                                        $sql    =   $mysqli->prepare("SELECT * FROM user WHERE gmail_id = ? LIMIT 1");

                                }else if($signin_type == "apple"){

                                        $sql    =   $mysqli->prepare("SELECT * FROM user WHERE apple_id = ? LIMIT 1");

                                }    
                                    
                                $sql->bind_param("s", $signin_id);
                                $sql->execute();
                                $query  =   $sql->get_result();

                                if($query->num_rows>0){

                                        

                                        if ($Row = $query->fetch_assoc()) { 

                                                $access_token           =   password_hash($Row['id'].'-'.date("YmdHis"),PASSWORD_DEFAULT);
                                                //$Row['access_token']    =   $access_token;
                                                $Row['avatar']          =   SITE_URL.$Row['avatar'];
                                                $Result['status']       =   'Success';
                                                $Result['message']      =   'Signin Success';
                                                $Result['total_found']  =   $query->num_rows;
                                                $Result['data']         =   $Row;

                                                //$sql    =   $mysqli->prepare("UPDATE user SET access_token = ? WHERE id = ?");
                                                //$sql->bind_param("si", $Row['access_token'] ,$Row['id']);
                                                //$sql->execute();
                                        

                                        }else{

                                                $Result['message']      = 'Signin Failed';

                                        }   


                                }else{

                                        $email          =   secure_input($_POST['signin_email']);
                                        $createdate     =   date("Y-m-d H:i:s");
                                        $create_ip      =   $_SERVER["REMOTE_ADDR"];
                                        $access_token   =   password_hash($signin_id.'-'.date("Y-m-d H:i:s"),PASSWORD_DEFAULT);
                                        $verify_code    =   rand(1000,9999);
                                        $verify_status  =   'Yes';
                    

                                        $sql    =   $mysqli->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
                                        $sql->bind_param("s", $email);
                                        $sql->execute();
                                        $query  =   $sql->get_result();
        
                                        if($query->num_rows>0){

                                                    $Row    =   $query->fetch_assoc();

                                                    if($signin_type == "facebook"){

                                                            $sql    =   $mysqli->prepare("UPDATE user SET facebook_id = ? , verify_status = 'Yes' WHERE id = ?");
                                                    
                                                    }else if($signin_type == "gmail"){
                    
                                                            $sql    =   $mysqli->prepare("UPDATE user SET gmail_id = ? , verify_status = 'Yes' WHERE id = ?");

                                                    }else if($signin_type == "apple"){
                                                    
                                                            $sql    =   $mysqli->prepare("UPDATE user SET apple_id = ? , verify_status = 'Yes' WHERE id = ?");

                                                    } 

                                                    $sql->bind_param("si", $signin_id,$Row['id']);
                                                    $sql->execute();
                                                    
                                                    $insert_id      =   $Row['id'];
                                        }else{

                                                    if($signin_type == "facebook"){

                                                            $sql    =   $mysqli->prepare("INSERT INTO user (facebook_id,email,createdate,create_ip,access_token,verify_code,verify_status) VALUES(?,?,?,?,?,?,?)");
                                                    
                                                    }else if($signin_type == "gmail"){
                    
                                                            $sql    =   $mysqli->prepare("INSERT INTO user (gmail_id,email,createdate,create_ip,access_token,verify_code,verify_status) VALUES(?,?,?,?,?,?,?)");
                                                    
                                                    }else if($signin_type == "apple"){
                                                    
                                                            $sql    =   $mysqli->prepare("INSERT INTO user (apple_id,email,createdate,create_ip,access_token,verify_code,verify_status) VALUES(?,?,?,?,?,?,?)");
                                                    
                                                    } 

                                                    $sql->bind_param("sssssss", $signin_id,$email,$createdate,$create_ip,$access_token,$verify_code,$verify_status);
                                                    $sql->execute();

                                                    $insert_id      =   $sql->insert_id;  
                                        }            



                                        if($insert_id>0){

                                                $Result['status']       = 'Success';
                                                $Result['message']      = 'Signin Success';
                                                
                            
                                                $sql    =   $mysqli->prepare("SELECT * FROM user WHERE id = ? LIMIT 1");
                                                $sql->bind_param("i", $insert_id);
                                                $sql->execute();
                                                $query  =   $sql->get_result();
                                                $Row    =   $query->fetch_assoc();
                            
                                                $Row['avatar']          =   SITE_URL.$Row['avatar'];
                                                $Result['data']         =   $Row;

                                        }else{

                                                $Result['message']      = 'Signin Failed';
                                        }



                                }  



                    }


    }else{

            $Result['message']      = 'Can not cannect database';
    }    


}else{

    $Result['message']      = 'Missing Variable';

}

echo json_encode($Result);

?>