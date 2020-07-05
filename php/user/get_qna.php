<?php
////////////////////获取密保问题////////////////////
//
// 功能  获取密保问题
// 输入  [POST] key, <value(un)>
//       <[COOKIE] uid>
// 输出  [JSON] state="success", uid, q1, q2
//              state="no-result"
//

require '../library.php';
//ini_set('display_errors',1);

$key = $_POST["key"];
$value = ($key == "un" ? "'".$_POST["un"]."'" : $_COOKIE["uid"]);

$json = array("state"=>"success");
if($conn = sql_link("user", "ys_users")) {
    $key = $conn->real_escape_string($key);
    $value = $conn->real_escape_string($value);
    $sql = "SELECT q1, q2 FROM qna WHERE $key=$value";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["q1"] = $result["q1"];
        $json["q2"] = $result["q2"];
    } else {
        $sql = "SELECT uid FROM users WHERE $key=$value";
        if($result = $conn->query($sql)->fetch_assoc()) {
            $uid = $result["uid"];
            $sql = "INSERT INTO qna (uid) VALUES ($uid)";
            if($conn->query($sql) === true) {
                $json["q1"] = null;
                $json["q2"] = null;
            } else {$json = array("state"=>"fail", "code"=>3, "message"=>$conn->error);}    
        } else {$json = array("state"=>"fail", "code"=>2, "message"=>"查无此人");}
    } 
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败");}
echo json_encode($json);
