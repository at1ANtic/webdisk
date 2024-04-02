<?php
error_reporting(0);
header("Content-Type:text/html;charset=utf8");

// 获取JWT Token（假设存储在Cookie中）
$jwt_token = isset($_COOKIE['jwt_token']) ? $_COOKIE['jwt_token'] : null;

// 验证JWT Token是否存在
if (!$jwt_token) {
    echo json_encode(['error' => '未提供JWT Token']);
    exit;
}

// 解码JWT Token获取用户名
$token_parts = explode('.', $jwt_token);
$token_payload = json_decode(base64_decode($token_parts[1]), true);
$username = $token_payload['username'];

// 获取要下载的文件名（假设以GET方式传递）
$filename = isset($_GET['filename']) ? $_GET['filename'] : null;

// 验证文件名是否存在
if (!$filename) {
    echo json_encode(['error' => '未提供要下载的文件名']);
    exit;
}

// 验证JWT Token是否为数据库中的用户
// （在实际应用中，应该使用更安全的方式，这里仅为演示）
include 'config.php'; // 包含数据库连接配置文件

// 创建数据库连接
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

// 检查连接是否成功
if ($conn->connect_error) {
    echo json_encode(['error' => '数据库连接失败: ' . $conn->connect_error]);
    exit;
}

// 获取base64加密后的用户名
$encoded_username = base64_encode($username);
$encoded_filename = urlencode($filename);

// 构建文件路径
$file_path = 'upload/' . $encoded_username . '/' . $encoded_filename;

// 检查文件是否存在
if (!file_exists($file_path)) {
    echo json_encode(['error' => '文件不存在']);
    exit;
}

// 设置响应头
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// 输出文件内容
readfile($file_path);
exit;

// 关闭数据库连接
$conn->close();
?>
