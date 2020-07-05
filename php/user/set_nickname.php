<?php
////////////////////设置昵称////////////////////
//
// 功能  设置昵称
// 输入  [POST] nickname
//       [COOKIE] uid, token
// 输出  [JSON] state
// 
require '../library.php';

$nickname = $_POST["nickname"];
$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

if(check($uid, $token)) {
    if($conn = sql_link("user", "judge")) {
        $nickname = $conn->real_escape_string($nickname);
        $sql = "UPDATE userSettings SET nickname='$nickname' WHERE uid=$uid";
        if($conn->query($sql) === true) {
            setcookie("nickname", $nickname, time()+31536000, "/");
            $json = array("state"=>"success");
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"更新数据失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}    
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"身份验证失败");}    
echo json_encode($json);
