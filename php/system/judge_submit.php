<?php
////////////////////评教////////////////////
//
// 功能  处理评教动作：添加评教记录、更新评教数据、[生成文本文档、创建审核事务]
// 输入  [POST] course, teacher, judge, describe
//       [Cookie] uid, token
// 输出  [JSON] state, [id]
//
require '../library.php';
//ini_set('display_errors',1);

$codeCourse = $_POST["course"];
$codeTeacher = $_POST["teacher"];
$judge = $_POST["judge"];
$describe = $_POST["describe"];
$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

$json["state"] = "success";
if($conn = sql_link("user", "judge")) {
    $codeCourse = $conn->real_escape_string($codeCourse);
    $codeTeacher = $conn->real_escape_string($codeTeacher);
    $judge = $conn->real_escape_string($judge);
    $uid = $conn->real_escape_string($uid);
    //$token = $conn->real_escape_string($token);
    if(check($uid, $token)) {
        // 添加记录 judge
        $sql = "SELECT detail3 FROM actions WHERE uid=$uid AND action='judge' AND detail1='$codeCourse' AND detail2='$codeTeacher'";
        if($result = $conn->query($sql)->fetch_assoc()) {
            $flag = true;
            $lastJudge = explode(',', $result["detail3"]);
        } else {
            $flag = false;
        }
        
        // 更新数据（读取、计算、更新）
        $sql = "SELECT judgeScore, judgeQuality, judgeEasy, judgeNumber FROM record WHERE codeCourse='$codeCourse' AND codeTeacher='$codeTeacher'";
        if($result = $conn->query($sql)->fetch_assoc()) {
            $newJudge = explode(',', $judge);
            if($flag) {
                $newJudgeNumber = (int)$result["judgeNumber"];
                $newJudgeScore = (float)($result["judgeScore"] * $result["judgeNumber"] + $newJudge[0] - $lastJudge[0]) / $newJudgeNumber;
                $newJudgeQuality = (float)($result["judgeQuality"] * $result["judgeNumber"] + $newJudge[1] - $lastJudge[1]) / $newJudgeNumber;
                $newJudgeEasy = (float)($result["judgeEasy"] * $result["judgeNumber"] + $newJudge[2] - $lastJudge[2]) / $newJudgeNumber;
                $sql = "UPDATE actions SET detail3='$judge', lastUpdate=NOW() WHERE uid=$uid AND action='judge' AND detail1='$codeCourse' AND detail2='$codeTeacher'";
            } else {
                $newJudgeNumber = (int)$result["judgeNumber"] + 1;
                $newJudgeScore = (float)($result["judgeScore"] * $result["judgeNumber"] + $newJudge[0]) / $newJudgeNumber;
                $newJudgeQuality = (float)($result["judgeQuality"] * $result["judgeNumber"] + $newJudge[1]) / $newJudgeNumber;
                $newJudgeEasy = (float)($result["judgeEasy"] * $result["judgeNumber"] + $newJudge[2]) / $newJudgeNumber;
                $sql = "INSERT INTO actions (uid, action, detail1, detail2, detail3) VALUES ($uid, 'judge', '$codeCourse', '$codeTeacher', '$judge')";
            }
            if($result = $conn->query($sql)) {
                $sql = "UPDATE record SET judgeScore=$newJudgeScore, judgeQuality=$newJudgeQuality, judgeEasy=$newJudgeEasy, judgeNumber=$newJudgeNumber WHERE codeCourse='$codeCourse' AND codeTeacher='$codeTeacher'";
                if($result = $conn->query($sql)) {
                    if(countAction($uid, "judge", 1)) {
                        if($describe) {
                            // 添加记录 describe
                            $sql = "INSERT INTO actions (uid, action, detail1, detail2, state) VALUES ($uid, 'describe', '$codeCourse', '$codeTeacher', 'pending')";
                            if($result = $conn->query($sql)) {
                                $id = $conn->insert_id;
                                $filePath = "../../data/actions/$id.txt";
                                $sql = "UPDATE actions SET detail3='$filePath' WHERE id=$id";
                                if($result = $conn->query($sql)) {
                                    if(file_put_contents($filePath, $describe)) {
                                        $json["id"] = $id;
                                    } else {$json = array("state"=>"fail", "code"=>8, "message"=>"写入记录失败");}
                                } else {$json = array("state"=>"fail", "code"=>7, "message"=>"更新记录失败");}
                            } else {$json = array("state"=>"fail", "code"=>6, "message"=>"添加操作记录失败");}
                        }
                    } else {$json = array("state"=>"fail", "code"=>9, "message"=>"更新统计数据失败");}
                } else {$json = array("state"=>"fail", "code"=>5, "message"=>"更新数据失败");}
            } else {$json = array("state"=>"fail", "code"=>3, "message"=>"添加操作记录失败");}
        } else {$json = array("state"=>"fail", "code"=>4, "message"=>"读取数据失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"登陆状态无效");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败");}
echo json_encode($json);
