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
    if($file = fopen("data\\teacher_2019-2020-1.txt", "r")) { // Consider about rt mode
        $count_total = 0; $count_success = 0; $count_skip = 0; $count_fail = 0;
        echo "开始处理 Teacher<br>";
        while(!feof($file)) {
            $item = fgets($file);
            $quote_times = substr_count($item, '"') / 2;
            if($quote_times == 0) {
                $people_number = 1;
            } else {
                $comma_times = substr_count($item, ',');
                $people_number = ($comma_times - 3) / $quote_times + 1;
            }
            $item = str_replace("\"", "", $item);
            $arrays = explode(',', $item);
            for($i = 0; $i < $people_number; $i++) {
                $count_total++;
                $name = $arrays[$i];
                $code = $arrays[$i + $people_number];
                $school = $arrays[$i + $people_number * 2];
                $title = $arrays[$i + $people_number * 3] ?? "";
                //echo "$item:$comma_times:$people_number<br>";
                //echo $count_total.":".$name.":".$code.":".$school.":".$title."<br>";
                $sql = "SELECT id FROM teacher WHERE code='$code'";
                $result = $conn->query($sql)->fetch_row();
                if(!$result) {
                    $sql = "INSERT INTO teacher (name, school, title, code) VALUES ('$name', '$school', '$title', '$code')";
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
        }
        fclose($file);
        echo "任务完成<br>";
        echo "共处理 $count_total 条数据：成功 $count_success 条，跳过 $count_skip 条，失败 $count_fail 条<br>";
    } else echo "打开文件失败<br>";
} else echo "连接数据库失败<br>";
echo "程序结束<br>";
