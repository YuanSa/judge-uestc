<?php
////////////////////设置密保////////////////////
//
// 功能  设置密保
// 输入  [POST] password, key1, <key2>
//       [COOKIE] uid
// 输出  [JSON] state
// 

require '../library.php';
//ini_set('display_errors',1);

$type = $_GET["type"];
$uid = $_COOKIE["uid"];
$q1 = $_POST["q1"];
$a1 = $_POST["a1"];
$q2 = $_POST["q2"];
$a2 = $_POST["a2"];

if(
    ($type == "pw" && verifyViaPassword($uid, $_POST["key1"])) ||
    ($type == "qna" && verifyViaQNA($uid, $_POST["key1"], $_POST["key2"]))
) {
    if($conn = sql_link("user", "ys_users")) {
        $q1 = $conn->real_escape_string($q1);
        $a1 = $conn->real_escape_string($a1);
        $q2 = $conn->real_escape_string($q2);
        $a2 = $conn->real_escape_string($a2);
        $sql = "UPDATE qna SET q1='$q1', a1='$a1', q2='$q2', a2='$a2' WHERE uid=$uid";
        if($conn->query($sql) === true) {
            $json = array("state"=>"success");
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"更新数据失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"身份验证失败");}
echo json_encode($json);
