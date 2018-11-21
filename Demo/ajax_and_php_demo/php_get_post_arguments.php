<?php
// 其实不设置Content-type也没什么问题,前端直接拿到字符串解析.
// 如果不设置的话,默认是 Content-Type: text/html; charset=UTF-8
// 有兴趣自己注释看看chrome的抓包的Content-Type
header('Content-type: application/json');
// 获取前端传来参数
$username =  $_POST["username"];
$password =  $_POST["password"];
$arr = [
	"backend_username" => $username,
	"backend_password" => $password,
];
echo json_encode($arr);