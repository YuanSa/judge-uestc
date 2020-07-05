<?php
////////////////////获取用户设置////////////////////
//
// 功能  获取用户设置
// 输入  [POST] uid
// 输出  [JSON] state, num_judge, num_description, nickname, avatar, weight
//

require '../library.php';
//ini_set('display_errors',1);

$uid = $_POST["uid"];

$json = array("state"=>"success");
if($conn = sql_link("admin", "judge")) {
    $uid = $conn->real_escape_string($uid);
    $sql = "SELECT score_pair, text_course, text_pair FROM counters WHERE uid=$uid";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["numJudge"] = $result["score_pair"];
        $json["numDescription"] = $result["text_course"] + $result["text_pair"];
    } else {
        $sql = "INSERT INTO counters (uid) VALUES ($uid)";
        if($result = $conn->query($sql)) {
            $json["numJudge"] = 0;
            $json["numSescription"] = 0;
        } else {
            $json = array("state"=>"fail", "code"=>2, "message"=>"新建计数器失败");
            echo json_encode($json);
            exit;
        }
    }

    $sql = "SELECT nickname, avatar, weight FROM userSettings WHERE uid=$uid";
    if($result = $conn->query($sql)->fetch_assoc()) {
        $json["nickname"] = $result["nickname"];
        $json["avatar"] = $result["avatar"];
        $json["weight"] = $result["weight"];
    } else {
        $sql = "INSERT INTO userSettings (uid) VALUES ($uid)";
        if($result = $conn->query($sql)) {
            $json["nickname"] = "Nameless";
            $json["avatar"] = 12;
            $json["weight"] = "1,1,1";
        } else {
            $json = array("state"=>"fail", "code"=>2, "message"=>"新建计数器失败");
            echo json_encode($json);
            exit;
        }
    }
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败");}
echo json_encode($json);
