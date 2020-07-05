<?php
////////////////////删除重复值////////////////////
//
// 功能  通过将数据排序插入新表实现删除重复值
//
require 'library.php';
ini_set('display_errors',1); 

$f = (int)$_GET["f"];

if($conn = sql_link("admin", "judge")) {
    $num = 0; $num_success = 0; $num_fail = 0;
    $from = $f * 500;
    $sql = "SELECT codeCourse, codeTeacher, judgeScore, judgeQuality, judgeEasy, judgeNumber FROM record LIMIT $from, 500";
    $result = $conn->query($sql);
    //echo $conn->error;
    while($r = $result->fetch_assoc()){
        $num++;
        $course = $r["codeCourse"];
        $teachers = explode(',', $r["codeTeacher"]);
        array_multisort($teachers);
        $teachers = implode(',', $teachers);
        $sql = "INSERT IGNORE INTO recordNew (codeCourse, codeTeacher) VALUE ('$course', '$teachers')";
        if($conn->query($sql)) {
            $num_success++;
        } else {
            $num_fail++;
        }
    }
    echo "任务完成<br>";
    echo "[$f] 共处理 $num 条数据：成功 $num_success 条，失败 $num_fail 条。";
} else echo "连接数据库失败<br>";
echo "程序结束<br>";
