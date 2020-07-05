<?php
////////////////////设置权重////////////////////
//
// 功能  设置权重
// 输入  [POST] weight
//       [COOKIE] uid, token
// 输出  [JSON] state
// 
require '../library.php';

$weight = $_POST["weight"];
$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

if(check($uid, $token)) {
    if($conn = sql_link("user", "judge")) {
        $weight = $conn->real_escape_string($weight);
        $sql = "UPDATE userSettings SET weight='$weight' WHERE uid=$uid";
        if($conn->query($sql) === true) {
            setcookie("weight", $weight, time()+31536000, "/");
            $json = array("state"=>"success");
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"更新数据失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}    
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"身份验证失败");}    
echo json_encode($json);
