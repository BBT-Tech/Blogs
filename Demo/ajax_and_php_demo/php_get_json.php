<?php
header('Content-Type:application/json; charset=utf-8');
// file_get_contents获取前端传来的数据,json_decode是将json字符串解析为数组
$data = json_decode(file_get_contents('php://input'), true);

// var_dump($data);

$arr = [
	"backen_username" => $data["username"],
	"backen_password" => $data["password"],
	// "other_data" => [1,2,3]
];

// php数组转化为json字符串!
echo json_encode($arr);
