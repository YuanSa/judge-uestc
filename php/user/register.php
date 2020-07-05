<?php
////////////////////注册////////////////////
//
// 功能  用户注册
// 输入  [POST] email, un, token
// 输出  [JSON] state, uid, un, token
//
require '../library.php';

$avatarMax = 15;
$nicknamePool = [
    ["薛定谔", "爱因斯坦", "爱因斯坦", "爱因斯坦", "安培", "法拉第", "开尔文", "开普勒", "玛丽·居里", "麦克斯韦", "牛顿", "普朗克", "普朗克", "特斯拉", "赫兹"],
    ["猫", "智慧", "坚毅", "发现", "苹果", "微积分", "假发", "预言", "镭", "方程组", "跑车", "交流电", "功劳", "学生"]
];

$un = $_POST["un"];
$pw = $_POST["pw"];

$illegal = !$un || !$pw;
if(!$illegal) {
    if($conn = sql_link("user","ys_users")) {
        $un = $conn->real_escape_string($un);
        $sql = "SELECT uid FROM users WHERE un='$un'";
        $result = $conn->query($sql)->fetch_assoc();
        if(!$result) {
            $pw = password_hash($pw, PASSWORD_DEFAULT);
            $token = mt_rand(0,999999);
            $sql = "INSERT INTO users (un, pw, token, expire)
                    VALUES ('$un','$pw','$token', DATE_ADD(NOW(), INTERVAL 30 DAY))";
            if($conn->query($sql) === true) {
                $sql = "SELECT LAST_INSERT_ID()";
                $uid = $conn->query($sql)->fetch_row()[0];
                $sql = "INSERT INTO qna (uid) VALUES ($uid) ";
                if($conn->query($sql) === true) {
                    $conn->close();
                    if($conn = sql_link("admin","judge")) {
                        $avatar = rand(1,$avatarMax);
                        $nickname = $nicknamePool[0][$avatar-1]."的".$nicknamePool[1][array_rand($nicknamePool[1])];
                        $sql = "INSERT INTO userSettings (uid, nickname, avatar) VALUES ($uid, '$nickname', $avatar)";
                        if($conn->query($sql) === true) {
                            $sql = "INSERT INTO counters (uid) VALUES ($uid)";
                            if($conn->query($sql) === true) {
                                setcookie("un", $un, time()+31536000, "/");
                                setcookie("uid", $uid, time()+31536000, "/");
                                setcookie("token", $token, time()+31536000, "/");
                                setcookie("avatar", $avatar, time()+31536000, "/");
                                setcookie("nickname", $nickname, time()+31536000, "/");
                                setcookie("weight", "1,1,1", time()+31536000, "/");
                                setcookie("login", "true", time()+31536000, "/");
                                $json = array("state"=>"success", "uid"=>$uid, "token"=>$token);
                            } else {$json = array("state"=>"fail", "code"=>8, "message"=>"无法插入用户统计记录：$conn->error");}
                        } else {$json = array("state"=>"fail", "code"=>7, "message"=>"无法插入用户设置记录：$conn->error");}
                    } else {$json = array("state"=>"fail", "code"=>6, "message"=>"无法连接到数据库：$conn->error");}
                } else {$json = array("state"=>"fail", "code"=>5, "message"=>"无法创建密保记录：$conn->error");}
            } else {$json = array("state"=>"fail", "code"=>4, "message"=>"无法创建用户记录：$conn->error");}
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"该用户名已被占用");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"无法连接到数据库：$conn->error");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"数据不完整");}

echo json_encode($json);
exit;