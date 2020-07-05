<?php
////////////////////获取待审核详情详情////////////////////
//
// 功能  获取待审核详情
// 输入  [COOKIE] uid, token
// 输出  [JSON] state, id, action, course, [teacher], text, date
//
//ini_set('display_errors',1);
require '../library.php';

$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

$json["state"] = "fail";
if($conn = sql_link("guest", "judge")) {    
    if(check($uid, $token) == "admin") {
        $sql = "SELECT * FROM actions WHERE state='pending' LIMIT 1";
        if($result = $conn->query($sql)) {
            $result = $result->fetch_assoc();
            if($result) {
                $json["id"] = $result["id"];
                $action = $result["action"];
                $json["action"] = $action;
                $json["date"] = $result["date"];
                $json["course"] = $result["detail1"];
                if($action == "describe") {
                    $file_add = $result["detail3"];
                    $json["teacher"] = $result["detail2"];
                } else if($action == "describeCourse") {
                    $file_add = $result["detail2"];
                } else {
                    $file_add = "详情不存在";
                }
                $file = fopen($file_add, "r") or die("无法打开待审核文件：$file_add");
                $json["text"] = fread($file,filesize($file_add));
                fclose($file);
                $json["state"] = "success";
            } else {$json = array("state"=>"fail", "code"=>4, "message"=>"任务已空，感谢您的付出！");}   
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"数据库查询失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"您没有权限访问本页面");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败");}
echo json_encode($json);
