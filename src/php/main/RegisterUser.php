<?php
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 判断用户名和密码是否为空
    if (empty($_POST['username']) || empty($_POST['password'])) {
        echo "用户名和密码不能为空";
        // 可以选择中断注册流程或者返回错误信息
        return;
    }

    // 处理注册请求
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 哈希密码

    // 数据库连接配置
    include 'config.php';

    // 创建数据库连接
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }

    // 检查用户名是否已存在
    $checkUsernameQuery = "SELECT * FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkUsernameQuery);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "用户名已存在，请选择其他用户名。";
    } else {
        // 使用参数化查询插入用户信息
        $insertQuery = "INSERT INTO users (username, password) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ss", $username, $password);

        if ($insertStmt->execute()) {
            echo "用户注册成功！";

            // 创建用户文件夹
            $uploadDirectory = 'upload/' . base64_encode($username);

            // 检查文件夹是否存在，如果不存在则创建
            if (!file_exists($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
                echo "用户文件夹创建成功！";

                // 设置文件夹权限（例如，设置为 755）
                chmod($uploadDirectory, 0755);
            } else {
                echo "用户文件夹已存在！";
            }
        } else {
            echo "Error: " . $insertStmt->error;
        }
    }

    // 关闭数据库连接
    $checkStmt->close();
    $insertStmt->close();
    $conn->close();
} else {
    echo "无效的请求方式！";
}
?>
