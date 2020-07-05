<?php
//ini_set('display_errors',1);

$device = "PC";

function sql_safety($conn, &...$arguments) {
    foreach ($arguments as &$argument_safe) {
        $argument_safe = $conn->real_escape_string($argument_safe);
    }
}

function sql_link($type, $database) {
    $servername = "cdb-2i6ogv6q.cd.tencentcdb.com:10007";
    switch($type) {
        // You should fill in the username & psw for different roles
        case "admin":
            $username = "";
            $password = "";
            break;
        case "user":
            $username = "";
            $password = "";
            break;
        case "guest":
            $username = "";
            $password = "";
            break;
        case "msgEditor":
            $username = "";
            $password = "";
            break;
    }
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        return false;
    } else {
        return $conn;
    }
}

function check($uid, $token, $conn=null) {
    $conn = $conn ?? sql_link("guest", "ys_users");
    sql_safety($conn, $uid, $token);
    $sql = "SELECT token, power, expire FROM users WHERE uid=$uid";
    if($result = $conn->query($sql)) {
        $result = $result->fetch_assoc();
        if($result["token"] == $token) {
            if(strtotime($result["expire"])-time()>0) {
                return $result["power"];
            }
        }
    }
    return false;
}

function verifyViaPassword($uid, $pw, $conn=null) {
    if($conn = $conn ?? sql_link("user", "ys_users")) {
        sql_safety($conn, $uid);
        $sql = "SELECT pw FROM users WHERE uid=$uid";
        if($result = $conn->query($sql)->fetch_assoc())
            if(password_verify($pw,$result["pw"]))
                return true;
    }
    return false;
}

function verifyViaQNA($uid, $ans1, $ans2, $conn=null) {
    if($conn = $conn ?? sql_link("user", "ys_users")) {
        sql_safety($conn, $uid, $ans1, $ans2);
        $sql = "SELECT a1, a2 FROM qna WHERE uid=$uid";
        if($result = $conn->query($sql)->fetch_assoc())
            if($result["a1"] && $result["a2"])
                if($result["a1"]==$ans1 && $result["a2"]==$ans2)
                    return true;
    }
    return false;
}

function countAction($uid, $action, $delta) {
    if($conn = sql_link("admin", "judge")) {
        switch($action) {
            case "tag": $key = "tag"; break;
            case "judge": $key = "score_pair"; break;
            case "description_course": $key = "text_course"; break;
            case "description_pair": $key = "text_pair"; break;
            default: return false;
        }
        $sql = "SELECT $key FROM counters WHERE uid=$uid";
        if($result = $conn->query($sql)->fetch_assoc()) {
            $value = $result[$key] + $delta;
            $sql = "UPDATE counters SET $key=$value WHERE uid=$uid";
            if($result = $conn->query($sql) === true) {
                // 添加成就判断
                return true;
            }
        } else {
            $sql = "INSERT INTO counters (uid) VALUES ($uid)";
            if($conn->query($sql) === true) {
                return countAction($uid, $action, $delta);
            }
        }
    }
    return false;
}

function messageTo($uid, $text, $fromWho, $type="notice", $conn=null) {
    if($conn = $conn ?? sql_link("admin", "judge")) {
        sql_safety($conn, $uid, $text, $fromWho);
        $sql = "INSERT INTO messages (uid, text, fromWho, type) VALUES ($uid, '$text', $fromWho, '$type')";
        if($conn->query($sql)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// THE FOLLOWING FUNCTIONS ARE OUT-DATED

function ys_weight($uid, $delta) {
    global $SCHOOL_CODE;
    if($connU = sql_link("user", "ys_users")) {
        $uid = $connU->real_escape_string($uid);
        $delta = (int)$delta;
        $sql = "SELECT weight FROM counters WHERE uid=$uid";
        if($result = $connU->query($sql)->fetch_assoc()) {
            $newWeight = $result["weight"] + $delta;
            $sql = "UPDATE counters SET weight=$newWeight WHERE uid=$uid";
            if($connU->query($sql)) {
                $connS = sql_link("user", $SCHOOL_CODE);

                // connX = connect {U:User, S:School}
                //    rX = result  {R:Record, T:teacher, C:Course, Tag}

                // 为避免delta是负数导致SQL语法错误，不能使用 SET key = key + $delta，而要先读出原码手动计算新值，即使用 SET key = $newValue

                $sql = "SELECT id, s1, s2, s3, s4 FROM records WHERE uid=$uid AND type='teacher'"; //更新teacher表
                $rR = $connU->query($sql);
                while($rowR = $rR->fetch_assoc()) { //遍历每一条记录
                    $sql = "SELECT s1, s2, s3, s4, num FROM teacher WHERE id={$rowR['id']}";
                    $rT = $connS->query($sql)->fetch_assoc();
                    $newNum = $rT["num"] + $delta;
                    if($newNum!=0) {
                        $newS1 = ($rT["s1"]*$rT["num"] + $delta*$rowR["s1"]) / $newNum;
                        $newS2 = ($rT["s2"]*$rT["num"] + $delta*$rowR["s2"]) / $newNum;
                        $newS3 = ($rT["s3"]*$rT["num"] + $delta*$rowR["s3"]) / $newNum;
                        $newS4 = ($rT["s4"]*$rT["num"] + $delta*$rowR["s4"]) / $newNum;
                    } else {
                        $newS1 = $newS2 = $newS3 = $newS4 = 0;
                    }
                    $sql = "UPDATE teacher SET s1=$newS1, s2=$newS2, s3=$newS3, s4=$newS4, num=$newNum WHERE id={$rowR['id']}";
                    $connS->query($sql);
                }
                $sql = "UPDATE records SET weight = $newWeight WHERE uid=$uid AND type='teacher'";
                $connU->query($sql);

                $sql = "SELECT id, s1, s2, s3 FROM records WHERE uid=$uid AND type='course'"; //更新course表
                $rR = $connU->query($sql);
                while($rowR = $rR->fetch_assoc()) {
                    $sql = "SELECT s1, s2, s3, num FROM course WHERE id={$rowR['id']}";
                    $rC = $connS->query($sql)->fetch_assoc();
                    $newNum = $rC["num"] + $delta;
                    if($newNum!=0) {
                        $newS1 = ($rC["s1"]*$rC["num"] + $delta*$rowR["s1"]) / $newNum;
                        $newS2 = ($rC["s2"]*$rC["num"] + $delta*$rowR["s2"]) / $newNum;
                        $newS3 = ($rC["s3"]*$rC["num"] + $delta*$rowR["s3"]) / $newNum;
                    } else {
                        $newS1 = $newS2 = $newS3 = 0;
                    }
                    $sql = "UPDATE course SET s1=$newS1, s2=$newS2, s3=$newS3, num=$newNum WHERE id={$rowR['id']}";
                    $connS->query($sql);
                }
                $sql = "UPDATE records SET weight = $newWeight WHERE uid=$uid AND type='course'";
                $connU->query($sql);

                $sql = "SELECT id, tag FROM records WHERE uid=$uid AND type='tag'"; //更新properties表 (tag)
                $rR = $connU->query($sql);
                while($rowR = $rR->fetch_assoc()) {  //【【rTag 和 rR的关系重新捋一下】】
                    $sql = "SELECT calls, quiz, arduous, free, paper, design, experiment, presentation, mid_exam, final_exam, focus FROM properties WHERE id={$rowR['id']}";
                    $rTag = $connS->query($sql)->fetch_array();
                    foreach ($rTag as $key => $value) {$rTag[$key] += $delta * bit($rowR["tag"], $key);} //解码 decode
                    $sql = "SELECT num FROM properties WHERE id={$rowR['id']}";
                    $rTagNum = $connS->query($sql)->fetch_array();
                    $newNum = $rTagNum["num"] + $delta;
                    $sql = "UPDATE properties SET num=$newNum, calls=$rTag[0], quiz=$rTag[1], arduous=$rTag[2], free=$rTag[3], paper=$rTag[4], design=$rTag[5],
                        experiment=$rTag[6], presentation=$rTag[7], mid_exam=$rTag[8], final_exam=$rTag[9], focus=$rTag[10] WHERE id={$rowR['id']}";    
                    $connS->query($sql);
                }
                $sql = "UPDATE records SET weight = $newWeight WHERE uid=$uid AND type='tag'";
                $connU->query($sql);

                return true;
            }
        }
    }
    return false;
}

function ys_achievement ($uid, $type) {
    $conn = sql_link("user", "ys_users");
    $uid = $conn->real_escape_string($uid);
    switch($type) {
        case "star":
            $sql = "UPDATE flags SET verify_std=1 WHERE uid=$uid";
            $conn->query($sql);
            $sql = "UPDATE achievements SET star=1 WHERE uid=$uid";
            $conn->query($sql);
            $msg = "恭喜！您因为通过了学生认证而获得了“最亮的星”称号！";
            ys_message($uid, $msg);
            ys_weight($uid, 1);
            return true;
        case "describes":
            $sql = "SELECT describes FROM counters WHERE uid=$uid";
            $result = $conn->query($sql)->fetch_assoc();
            $flag = true;
            switch($result["describes"]) {
                case 3:
                    $sql = "UPDATE achievements SET pencile=1 WHERE uid=$uid";
                    $msg = "恭喜！您累计为3门课程添加了课程详情，被授予了“我有话说”称号！";
                    break;
                case 12:
                    $sql = "UPDATE achievements SET pen=1 WHERE uid=$uid";
                    $msg = "恭喜！您累计为12门课程添加了课程详情，被授予了“笔耕不辍”称号！";
                    break;
                case 24:
                    $sql = "UPDATE achievements SET superGreen=1 WHERE uid=$uid";
                    $msg = "恭喜！您累计为24门课程添加了课程详情，获得了记忆与文字女神——谟涅摩叙涅的庇佑！";
                    ys_weight($uid, 3);
                    break;
                default: $flag = false;
            }
            if($flag) {
                $conn->query($sql);
                ys_message($uid, $msg);
            }
            return true;
        case "tag":
            $sql = "SELECT tag FROM counters WHERE uid=$uid";
            $result = $conn->query($sql)->fetch_assoc();
            if($result["tag"] == 30) {
                $sql = "UPDATE achievements SET tag=1 WHERE uid=$uid";
                $msg = "恭喜！您累计为30门课程打了标签，被授予了“标签达人”称号！";
                $conn->query($sql);
                ys_message($uid, $msg);
            }
            return true;
        case "rate":
            $sql = "SELECT rate FROM counters WHERE uid=$uid";
            $result = $conn->query($sql)->fetch_assoc();
            if($result["rate"] == 60) {
                $sql = "UPDATE achievements SET superYellow=1 WHERE uid=$uid";
                $msg = "恭喜！您累计评教60次，获得了公正女神——忒弥斯的庇佑！";
                $conn->query($sql);
                ys_weight($uid, 3);
                ys_message($uid, $msg);
            }
            return true;
    }
    return false;
}