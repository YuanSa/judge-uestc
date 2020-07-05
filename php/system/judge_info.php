<?php
////////////////////获取评教信息////////////////////
//
// 功能  获取评教页面所需信息（课程名、老师名、老师学院、老师职称）
// 输入  [POST] code
// 输出  [JSON] state, name, teachers[name, detail], description, scores
//
require '../library.php';
//ini_set('display_errors',1);    

$codeCourse = $_POST["course"];
$codeTeacher = $_POST["teacher"];
$uid = $_COOKIE["uid"];

$json = array("state"=>"success");
if($conn = sql_link("guest", "judge")) {
    // Course Part
    $codeCourse = $conn->real_escape_string($codeCourse);
    $sql = "SELECT name FROM course WHERE code='$codeCourse'";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["name"] = $result["name"];
    } else $json = array("state"=>"fail", "code"=>2, "message"=>"没有找到编号为'$codeCourse'的课程记录");
    
    // Teacher Part
    $codeTeacher = $conn->real_escape_string($codeTeacher);
    $json["teachers"] = [];
    $i = 0;
    $codeTeacherList = implode("','",explode(',', $codeTeacher));
    $sql = "SELECT name, school, title FROM teacher WHERE code IN ('$codeTeacherList')";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        $json["teachers"][$i]["name"] = $row["name"];
        $json["teachers"][$i]["detail"] = $row["school"]." ".$row["title"];
        $i++;
    }
    $json["description"] = file_get_contents("../../data/course-teacher/$codeCourse-$codeTeacher.txt");
    if(!$json["description"]) {
        $json["description"] = "";
    }

    // Score Part
    $sql = "SELECT detail3 FROM actions WHERE uid=$uid AND action='judge' AND detail1='$codeCourse' AND detail2='$codeTeacher'";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["scores"] = $result["detail3"];
    } else {$json["scores"] = 0;}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败");}
echo json_encode($json);
