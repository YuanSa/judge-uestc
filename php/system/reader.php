<?php
////////////////////获取文章////////////////////
//
// 功能  获取评教页面所需信息（课程名、老师名、老师学院、老师职称）
// 输入  [POST] code
// 输出  [JSON] state, name, teachers[name, detail], description, scores
//
require '../library.php';
//ini_set('display_errors',1);    

$index = $_POST["i"];

$permission = true;
switch($index) {
    case 1: $title = "服务协议"; break;
    case 2: $title = "联系与加入"; break;
    case 3: $title = "BUG汇总"; break;
    default: $permission = false;
}

if($permission) {
    $json["title"] = $title;
    $text = file_get_contents("../../data/essay/$title.txt");
    if($text) {
        $json = array("state"=> "success", "title" => $title, "text" =>$text);
    } else {$json = array("state"=> "fail", "code" => 2, "message" =>"没有找到文章");}
} else {$json = array("state"=> "fail", "code" => 1, "message" =>"禁止访问的资源：传入参数($index)不在许可列表中。");}
echo json_encode($json);
