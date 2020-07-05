<?php
////////////////////设置密码////////////////////
//
// 功能  设置密码
// 输入  [POST] password, key1, <key2>
//       [COOKIE] uid
// 输出  [JSON] state
// 

require '../library.php';
//ini_set('display_errors',1);

$type = $_GET["type"];
$uid = $_COOKIE["uid"];
$password = $_POST["password"];

if(
    ($type == "pw" && verifyViaPassword($uid, $_POST["key1"])) ||
    ($type == "qna" && verifyViaQNA($uid, $_POST["key1"], $_POST["key2"]))
) {
    if($conn = sql_link("user", "ys_users")) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET pw='$password' WHERE uid=$uid";
        if($conn->query($sql) === true) {
            $json = array("state"=>"success");
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"更新密钥失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}    
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"身份验证失败");}        
echo json_encode($json);
