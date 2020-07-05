<?php
ini_set('display_errors',1);
require 'library.php';

$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];
if(check($uid, $token) == "admin") {
    $conn = sql_link("guest", "ys_users");

    $test1 = "('s Hertogenbosch')";
    $test2 = ") UPDATE ys_user SET uid=0;";
    echo "Original Sring1: $test1<br>";
    echo "Original Sring2: $test2<br>";
    sql_safety($conn, $test1, $test2);
    echo "Modified Sring1: $test1<br>";
    echo "Modified Sring2: $test2<br>";

}
