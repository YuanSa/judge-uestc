<?php
ini_set('MAX_EXECUTION_TIME', '-1');
////////////////////导入课程////////////////////
//
// 功能  导入课程信息
//
require '../library.php';

//set_time_limit(0);
ob_end_clean();
ob_implicit_flush(1);

if($conn = sql_link("user", "judge")) {
    if($file = fopen("data\\pair_2019-2020-1.txt", "r")) { // Consider about rt mode
        $count_total = 0; $count_success = 0; $count_skip = 0; $count_fail = 0;
        echo "开始处理 Pair<br>";
        while(!feof($file)) {
            $count_total++;
            $item = fgets($file);
            $multiple = substr_count($item, '"') ? 1 : 0;
            if($multiple) {
                $item = str_replace('"', "", $item);
            }
            $people_number = substr_count($item, ',');
            $arrays = explode(',', $item);
            $cc = $arrays[0];
            $ct = $arrays[1];
            for($i = 2; $i <= $people_number; $i++) {
                $ct .= ",".$arrays[$i];
            }
            $sql = "SELECT code_course FROM record WHERE code_course='$cc' AND code_teacher='$ct'";
            $result = $conn->query($sql)->fetch_row();
            if(!$result) {
                $sql = "INSERT INTO record (code_course, code_teacher, multiple) VALUES ('$cc', '$ct', $multiple)";
                $conn->query($sql) ? $count_success++ : $count_fail++;
            } else {
                $count_skip++;
            }
        }
        fclose($file);
        echo "任务完成<br>";
        echo "共处理 $count_total 条数据：成功 $count_success 条，跳过 $count_skip 条，失败 $count_fail 条<br>";
    } else echo "打开文件失败<br>";
} else echo "连接数据库失败<br>";
echo "程序结束<br>";
