<?php
////////////////////广泛查询////////////////////
//
// 功能  搜索并获取限定数量的课程和老师的信息
// 日期  2018.12.16.21:17
// 输入  [GET ] s, p
// 输出  [JSON] state, pageMax, data
//
require '../library.php';
ini_set('display_errors',1);

$search = $_POST["s"];
$page = (int)$_POST["p"];

$numPerPage = 10;
$from = ($page-1) * $numPerPage;

$items = [];
if($conn = sql_link("guest","judge")) {
    $search = $conn->real_escape_string($search);
    $i = 0;
    $items = [];

    $sql = "SELECT name, code FROM course WHERE code='$search'";
    $result = $conn->query($sql);
    if($row = $result->fetch_assoc()) {
        $items[$i++] = $row;
    }

    $sql = "SELECT name, code FROM course WHERE name LIKE '%{$search}%' LIMIT $from, $numPerPage";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        $items[$i++] = $row;
    }

    $sql = "SELECT COUNT(*) AS num FROM course WHERE name LIKE '%{$search}%'";
    $num = $conn->query($sql)->fetch_assoc()['num'];
    $maxPage = ceil($num/$numPerPage);

    $json = array("state"=>"success", "maxPage"=>$maxPage, "data"=>$items);
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接学校数据库失败");}
echo json_encode($json);
exit;