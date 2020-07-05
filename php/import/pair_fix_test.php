<?php
////////////////////删除重复值////////////////////
//
// 功能  通过将数据排序插入新表实现删除重复值
//
require '../library.php';
//ini_set('display_errors',1); 

$in = "abc,003,001,xyz,002,bgh";
$teachers = explode(',', $in);
array_multisort($teachers);
$out = implode(',', $teachers);

echo $out;
