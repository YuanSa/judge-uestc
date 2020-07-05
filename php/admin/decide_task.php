<?php
////////////////////详情审核////////////////////
//
// 功能  审核用户添加的课程详情
// 输入  [POST] id, decision, note
//       [COOKIE] uid, token
// 输出  [JSON] state, message
//
ini_set('display_errors',1);
require '../library.php';

$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];
$id = $_POST["id"];
$decision = $_POST["decision"];
$note = $_POST["note"] ?? "";

if(check($uid, $token) == "admin") {
    if($conn = sql_link("admin", "judge")) {
        sql_safety($conn, $uid, $id, $note);
        $sql = "UPDATE actions SET state='".($decision=="true"?"accept":"deny")."', lastUpdate=NOW(), note='$note', sign='$uid' WHERE id=$id";
        if($conn->query($sql)) {
            if($decision == "true") {
                $sql = "SELECT uid, action, detail1, detail2, detail3 FROM actions WHERE id=$id";
                if($result = $conn->query($sql)) {
                    $result = $result->fetch_assoc();
                    $file_this = "../../data/actions/$id.txt";
                    if($result["action"]=="describe") {
                        $file_new = "../../data/course-teacher/".$result["detail1"]."-".$result["detail2"].".txt";
                        countAction($result["uid"], "description_pair", 1);
                    } elseif ($result["action"]=="describeCourse") {
                        $file_new = "../../data/course/".$result["detail1"].".txt";
                        countAction($result["uid"], "description_course", 1);
                    }
                }
                $file = fopen($file_this, "r") or die("无法读取文件");
                $text = fread($file,filesize($file_this));
                fclose($file);
                $file = fopen($file_new, "w") or die("无法写入文件");
                fwrite($file, $text);
                fclose($file);
                $msg = "您对课程".$result["detail1"]."的点评已生效";
                messageTo($result["uid"], $msg, $uid, "notice", $conn);
                $json = array("state"=>"success", "code"=>0, "message"=>"[Accepted] $id (Success)");
            } else {
                $sql = "SELECT uid, detail1, note FROM actions WHERE id=$id";
                if($result = $conn->query($sql)->fetch_assoc()) {
                    $msg = "抱歉，您对课程".$result["detail1"]."的点评审核未通过。管理员给出的原因是：\"".$result["note"]."\"。您用于申诉的存档号为$id 。";
                    messageTo($result["uid"], $msg, $uid, "notice", $conn);
                }
                $json = array("state"=>"success", "code"=>0, "message"=>"[Denied] $id (Success)");
            }
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"更新任务状态失败");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败：$conn->error");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"您没有权限访问本页面");}
echo json_encode($json);
exit;