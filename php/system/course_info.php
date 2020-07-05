<?php
////////////////////获取课程信息////////////////////
//
// 功能  获取课程的详细信息：课程名称、简介、考核方式、属性、老师的详细信息（名字、工号、评分、简介）
// 输入  [POST] code
// 输出  [JSON] state, name, assessment, description,
//              tags[num, examMid, examFinal, quiz, activities, thesis, focus, experiment, presentation, speak, attendance],
//              teachers[name[...], code[...], judge[...], description]
//
require '../library.php';

$codeCourse = $_POST["code"];

$json = array("state"=>"success");
if($conn = sql_link("guest", "judge")) {
    // Course Part
    $codeCourse = $conn->real_escape_string($codeCourse);
    $sql = "SELECT name, assessment FROM course WHERE code='$codeCourse'";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["name"] = $result["name"];
        $json["assessment"] = $result["assessment"];
        if(!($json["description"] = file_get_contents("../../data/course/$codeCourse.txt"))) {
            $json["description"] = "该课程还没有详情。你对本课程熟悉吗？不如来分享一下你的心路历程？";
        }
        $sql = "SELECT examMid, examFinal, quiz, activities, thesis, focus, experiment, presentation, speak, attendance, num FROM tags WHERE code='$codeCourse'";
        if($json["tags"] = $conn->query($sql)->fetch_assoc()) {
            ;
        } else $json = array("state"=>"fail", "code"=>3, "message"=>"没有找到编号为'$codeCourse'的课程的标签数据");
    } else $json = array("state"=>"fail", "code"=>2, "message"=>"没有找到编号为'$codeCourse'的课程记录");
    
    // Teacher Part
    $json["teachers"] = [];
    $i = 0;
    $sql = "SELECT codeTeacher, judgeScore, judgeQuality, judgeEasy, judgeNumber FROM record WHERE codeCourse='$codeCourse'";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        $names = [];
        $j = 0;
        $codeTeacher = explode(',', $row["codeTeacher"]);
        $codeTeacherList = implode("','",$codeTeacher);
        $sql = "SELECT name FROM teacher WHERE code IN ('$codeTeacherList')";
        $result2 = $conn->query($sql);
        while($row2 = $result2->fetch_assoc()) {
            $names[$j++] = $row2["name"];
        }
        $json["teachers"][$i]["name"] = implode("、",$names);
        $json["teachers"][$i]["code"] = $row["codeTeacher"];
        $json["teachers"][$i]["judge"]["score"] = $row["judgeScore"];
        $json["teachers"][$i]["judge"]["quality"] = $row["judgeQuality"];
        $json["teachers"][$i]["judge"]["easy"] = $row["judgeEasy"];
        $json["teachers"][$i]["judge"]["number"] = $row["judgeNumber"];
        if(!($json["teachers"][$i]["description"] = (file_get_contents("../../data/course-teacher/$codeCourse-{$row["codeTeacher"]}.txt")))) {
            $json["teachers"][$i]["description"] = "还没有人评论本老师的这门课程。你对TA上的这门课熟悉吗？来讲讲你的故事吧";
        }
        $i++;
    }
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败");}
echo json_encode($json);
