<?php
////////////////////设置用户名////////////////////
//
// 功能  设置昵称
// 输入  [POST] nickname
//       [COOKIE] uid
// 输出  [JSON] state
// 

require '../library.php';
//ini_set('display_errors',1);

$type = $_GET["type"];
$uid = $_COOKIE["uid"];
$username = $_POST["username"];

if(
    ($type == "pw" && verifyViaPassword($uid, $_POST["key1"])) ||
    ($type == "qna" && verifyViaQNA($uid, $_POST["key1"], $_POST["key2"]))
) {
    if($conn = sql_link("user", "ys_users")) {
        $username = $conn->real_escape_string($username);
        $sql = "UPDATE users SET un='$username' WHERE uid=$uid";
        if($conn->query($sql) === true) {
            setcookie("un", $username, time()+31536000, "/");
            $json = array("state"=>"success");
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"该用户名已存在");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}    
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"身份验证失败");}        
echo json_encode($json);
