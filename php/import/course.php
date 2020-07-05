<?php
////////////////////导入课程////////////////////
//
// 功能  导入课程信息
//
require '../library.php';

//set_time_limit(0);
ob_end_clean();
ob_implicit_flush(1);

if($conn = sql_link("user", "judge")) {
    if($file = fopen("data\course_2019-2020-1.txt", "r")) { // Consider about rt mode
        $count_total = 0; $count_success = 0; $count_skip = 0; $count_fail = 0;
        echo "开始处理<br>";
        while(!feof($file)) {
            $count_total++;
            $item = fgets($file);
            list($code, $name) = explode(',', $item);
            echo "($code)$name<br>";
            $sql = "SELECT id FROM course WHERE code='$code'";
            $result = $conn->query($sql)->fetch_row();
            echo "$result<br>";
            if(!$result) {
                $sql = "INSERT INTO course (name, code) VALUES ('$name', '$code')";
                if($conn->query($sql) == true) {
                    $count_success++;
                    //echo "成功添加记录：$name ($code)\n";
                } else {
                    $count_fail++;
                    //echo "添加记录失败：$name ($code)\n";
                }
            } else {
                $count_skip++;
                //echo "记录已存在：$name ($code)\n";
            }
        }
        fclose($file);
        echo "任务完成<br>";
        echo "共处理 $count_total 条数据：成功 $count_success 条，跳过 $count_skip 条，失败 $count_fail 条<br>";
    } else echo "打开文件失败<br>";
} else echo "连接数据库失败<br>";
echo "程序结束<br>";
