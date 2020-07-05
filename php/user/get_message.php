<?php
////////////////////获取用户信息////////////////////
//
// 功能  获取用户设置
// 输入  [COOKIE] uid, token
// 输出  [JSON] state, items
//
require '../library.php';

$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

if(check($uid, $token)) {
    if($conn = sql_link("admin", "judge")) {
        sql_safety($conn, $uid);
        $sql = "SELECT * FROM messages WHERE uid=$uid ORDER BY date DESC LIMIT 10";
        if($result = $conn->query($sql)) {
            $items = [];
            $i = 0;
            while($row = $result->fetch_assoc()) {
                $items[$i++] = $row;
            }
            $json = array("state"=>"success", "items"=>$items);
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"查询失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"验证用户身份失败");}
echo json_encode($json);
