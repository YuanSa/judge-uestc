<?php
////////////////////登陆////////////////////
//
// 功能  用户登录
// 输入  [POST] un, pw
// 输出  [JSON] state, uid, nickname, flags
// 
require '../library.php';

$un = $_POST["un"];
$pw = $_POST["pw"];

$illegal = !$un || !$pw;
if(!$illegal) {
    if($conn = sql_link("user", "ys_users")) {
        $un = $conn->real_escape_string($un);
        $sql = "SELECT uid, pw FROM users WHERE un='$un'";
        if($result = $conn->query($sql)->fetch_assoc()) {
            $pwHash = $result["pw"];
            $uid = $result["uid"];
            if(password_verify($pw,$pwHash)) {
                $token = mt_rand(0,999999);
                $sql = "UPDATE users SET token='$token', expire=DATE_ADD(NOW(), INTERVAL 365 DAY) WHERE uid=$uid";
                if ($conn->query($sql) === true) {
                    $conn->close();
                    if($conn = sql_link("user", "judge")) {
                        $settings = [];
                        $sql = "SELECT avatar, nickname, weight, authentification, studentAuthentification FROM userSettings WHERE uid=$uid";
                        if($result = $conn->query($sql)->fetch_assoc()) {
                            $settings = $result;
                        } else {
                            $msg = $conn->error;
                            $sql = "INSERT INTO userSettings (uid, nickname) VALUES ($uid, 'Nameless')";
                            if($result = $conn->query($sql)) {
                                $settings["avatar"] = 12;
                                $settings["nickname"] = "Nameless";
                                $settings["weight"] = "1,1,1";
                                $settings["authentification"] = null;
                                $settings["studentAuthentification"] = null;
                            } else {
                                $json = array("state"=>"fail", "code"=>7, "message"=>"为老用户创建新的个人设置记录失败");
                                echo json_encode($json);
                                exit;
                            }
                        }
                        setcookie("un", $un, time()+31536000, "/");
                        setcookie("uid", $uid, time()+31536000, "/");
                        setcookie("token", $token, time()+31536000, "/");
                        setcookie("login", "true", time()+31536000, "/");
                        setcookie("nickname", $settings["nickname"], time()+31536000, "/");
                        setcookie("avatar", $settings["avatar"], time()+31536000, "/");
                        setcookie("weight", $settings["weight"], time()+31536000, "/");
                        setcookie("authentification", $settings["authentification"], time()+31536000, "/");
                        setcookie("studentAuthentification", $settings["studentAuthentification"], time()+31536000, "/");
                        $json = array("state"=>"success", "message"=>$settings["avatar"]);
                    } else {$json = array("state"=>"fail", "code"=>6, "message"=>"连接数据库2失败");}
                } else {$json = array("state"=>"fail", "code"=>5, "message"=>"更新用户数据失败：$conn->error");}
            } else {$json = array("state"=>"fail", "code"=>4, "message"=>"密码错误");}
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"用户不存在");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"连接数据库失败");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"数据不完整");}

echo json_encode($json);
exit;