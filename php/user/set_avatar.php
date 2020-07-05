<?php
////////////////////设置头像////////////////////
//
// 功能  设置头像
// 输入  [POST] avatar
//       [COOKIE] uid, token
// 输出  [JSON] state
// 
require '../library.php';

$avatar = $_POST["avatar"];
$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

if(check($uid, $token)) {
    if($conn = sql_link("user", "judge")) {
        $avatar = $conn->real_escape_string($avatar);
        $sql = "UPDATE userSettings SET avatar=$avatar WHERE uid=$uid";
        if($conn->query($sql) === true) {
            setcookie("avatar", $avatar, time()+31536000, "/");
            $json = array("state"=>"success");
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"更新数据失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}    
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"身份验证失败");}    
echo json_encode($json);
