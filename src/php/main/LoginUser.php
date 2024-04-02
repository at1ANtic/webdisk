<?php

require_once 'vendor/autoload.php'; // 引入 JWT 库

use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 处理登录请求
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 数据库连接配置
    require_once 'config.php'; // 包含配置文件

    // 使用配置文件中的变量进行数据库连接
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }

    // 查询用户信息
    $sql = "SELECT username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 用户存在，检查密码
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        if (password_verify($password, $stored_password)) {
            // 密码匹配，生成JWT令牌
            $secret_key = 'aaa';
            $token = JWT::encode(['id' => $row['id'], 'username' => $row['username']], $secret_key, 'HS256');

            // 返回JSON数据给客户端
            echo json_encode(['success' => true, 'message' => '登录成功', 'token' => $token]);
        } else {
            echo json_encode(['success' => false, 'message' => '密码不正确']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '用户不存在']);
    }

    // 关闭数据库连接
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求方式']);
}

?>
