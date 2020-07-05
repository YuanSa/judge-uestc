<?php
////////////////////获取评教课程信息////////////////////
//
// 功能  获取评教页面所需信息（课程名、考核方式、当前评价）
// 输入  [POST] code
// 输出  [JSON] state, name, description
//
require '../library.php';
//ini_set('display_errors',1);    

$code = $_POST["code"];

$json = array("state"=>"success");
if($conn = sql_link("guest", "judge")) {
    // Course Part
    $code = $conn->real_escape_string($code);
    $sql = "SELECT name FROM course WHERE code='$code'";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["name"] = $result["name"];
        $json["description"] = file_get_contents("../../data/course/$code.txt");
        if(!$json["description"]) {
            $json["description"] = "";
        }
    } else $json = array("state"=>"fail", "code"=>2, "message"=>"没有找到编号为'$codeCourse'的课程记录");
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败：$conn->error");}
echo json_encode($json);
