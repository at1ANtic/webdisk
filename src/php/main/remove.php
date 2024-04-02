<?php
error_reporting(0);
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

include 'config.php'; // 包含数据库连接配置文件

// 创建数据库连接
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

// 检查连接是否成功
if ($conn->connect_error) {
    echo json_encode(['error' => '数据库连接失败: ' . $conn->connect_error]);
    exit;
}

// 使用预处理语句防止SQL注入
$delete_user_query = "DELETE FROM users WHERE username = ?";
$delete_user_statement = $conn->prepare($delete_user_query);
$delete_user_statement->bind_param('s', $username);
$delete_user_result = $delete_user_statement->execute();

if ($delete_user_result === true) {
    // 获取base64加密后的用户名
    $encoded_username = base64_encode($username);

    // 构建文件夹路径
    $folder_path = 'upload/' . $encoded_username;

    // 验证文件夹是否存在
    if (file_exists($folder_path)) {
        // 删除文件夹及其内容
        removeDir($folder_path);
    }

    // 清空Cookie
    setcookie('jwt_token', '', time() - 3600, '/'); // 设置过期时间为当前时间的前一小时

    echo json_encode(['message' => '账号删除成功']);
    header("Location: index.html"); 
    exit;
} else {
    echo json_encode(['error' => '账号删除失败']);
    header("Location: control.php"); 
    exit;
}

// 关闭数据库连接
$conn->close();

// 辅助函数：递归删除文件夹及其内容
function removeDir($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? removeDir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
?>
