<?php
////////////////////评教////////////////////
//
// 功能  处理评教动作：添加评教记录、更新评教数据、[生成文本文档、创建审核事务]
// 输入  [POST] code, tags, description
//       [Cookie] uid, token
// 输出  [JSON] state, [id]
//
require '../library.php';
//ini_set('display_errors',1);

$code = $_POST["code"];
$tags = $_POST["tags"];
$description = $_POST["description"];
$uid = $_COOKIE["uid"];
$token = $_COOKIE["token"];

$json["state"] = "success";
if($conn = sql_link("user", "judge")) {
    $code = $conn->real_escape_string($code);
    $tags = (int)$conn->real_escape_string($tags);
    $uid = $conn->real_escape_string($uid);
    //$token = $conn->real_escape_string($token);
    for($i = 0; $i < 11; $i++) {
        $setTag[$i] = $tags & (1 << $i) ? 1 : 0;
    }
    if(check($uid, $token)) {
        // 添加记录 judge
        $lastTag = [];
        $sql = "SELECT detail2 FROM actions WHERE uid=$uid AND action='tag' AND detail1='$code'";
        if($result = $conn->query($sql)->fetch_assoc()) {
            $flag = true;
            $last = (int)$result["detail2"];
            for($i = 0; $i < 11; $i++) {
                $lastTag[$i] = $last & (1 << $i) ? 1 : 0;
            }
            $sql = "UPDATE actions SET detail2='$tags' WHERE uid=$uid AND action='tag' AND detail1='$code'";
        } else {
            $flag = false;
            for($i = 0; $i < 11; $i++) {
                $lastTag[$i] = 0;
            }
            $sql = "INSERT INTO actions (uid, action, detail1, detail2, state) VALUES ($uid, 'tag', '$code', '$tags', 'done')";
        }
        if($conn->query($sql)) {
            $sql = "SELECT examMid, examFinal, quiz, activities, thesis, focus, experiment, presentation, speak, attendance, discuss, num FROM tags WHERE code='$code'";
            if($result = $conn->query($sql)->fetch_array()) {
                for($i = 0; $i < 11; $i++) {
                    $nowTag[$i] = $result[$i];
                }
                $nowNum = $result[$i];

                for($i = 0; $i < 11; $i++) {
                    $newTag[$i] = $nowTag[$i] - $lastTag[$i] + $setTag[$i];
                }
                $newNum = $nowNum + ($flag ? 0 : 1);
                $i = 0;
                $sql = "UPDATE tags SET examMid={$newTag[$i++]}, examFinal={$newTag[$i++]}, quiz={$newTag[$i++]}, activities={$newTag[$i++]},
                        thesis={$newTag[$i++]}, focus={$newTag[$i++]}, experiment={$newTag[$i++]}, presentation={$newTag[$i++]}, speak={$newTag[$i++]},
                        attendance={$newTag[$i++]}, discuss={$newTag[$i++]}, num={$newNum} WHERE code='$code'";
                if($result = $conn->query($sql)) {
                    if(countAction($uid, "tag", 1)) {
                        if($description) {
                            // 添加记录 description
                            $sql = "INSERT INTO actions (uid, action, detail1, state) VALUES ($uid, 'describeCourse', '$code', 'pending')";
                            if($result = $conn->query($sql)) {
                                $id = $conn->insert_id;
                                $filePath = "../../data/actions/$id.txt";
                                $sql = "UPDATE actions SET detail2='$filePath' WHERE id=$id";
                                if($result = $conn->query($sql)) {
                                    if(file_put_contents($filePath, $description)) {
                                        $json["id"] = $id;
                                    } else {$json = array("state"=>"fail", "code"=>9, "message"=>"写入文件失败");}
                                } else {$json = array("state"=>"fail", "code"=>8, "message"=>"更新记录失败：$conn->error");}
                            } else {$json = array("state"=>"fail", "code"=>7, "message"=>"添加操作记录失败：$conn->error");}
                        }
                    } else {$json = array("state"=>"fail", "code"=>6, "message"=>"更新统计数据失败：$conn->error");}
                } else {$json = array("state"=>"fail", "code"=>5, "message"=>"更新数据失败：$conn->error");}
            } else {$json = array("state"=>"fail", "code"=>4, "message"=>"读取数据失败：$conn->error");}
        } else {$json = array("state"=>"fail", "code"=>3, "message"=>"插入标签记录失败：$conn->error");}
    } else {$json = array("state"=>"fail", "code"=>2, "message"=>"登陆状态无效");}
} else {$json = array("state"=>"fail", "code"=>1, "message"=>"连接数据库失败：$conn->error");}
echo json_encode($json);
