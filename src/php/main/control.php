<?php
error_reporting(0);
header("Content-Type:text/html;charset=utf8");

function folderSize($directory)
{
    $totalSize = 0;

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        $totalSize += $file->getSize();
    }

    return $totalSize;
}

// 获取JWT Token（假设存储在Cookie中）
if (isset($_COOKIE['jwt_token'])) {
    $jwt_token = $_COOKIE['jwt_token'];

    // 解码JWT Token获取用户名
    $token_parts = explode('.', $jwt_token);
    $token_payload = json_decode(base64_decode($token_parts[1]), true);
    $username = $token_payload['username'];

    // 检查JWT Token是否为数据库中的用户
    // （在实际应用中，应该使用更安全的方式，这里仅为演示）
    include 'config.php'; // 包含数据库连接配置文件

    // 创建数据库连接
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }

    // 查询数据库中是否存在该用户
    $check_user_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $check_user_result = $stmt->get_result();

    if ($check_user_result->num_rows === 0) {
        // 如果用户不存在，输出错误信息
        echo "用户不存在，请重新登录！";
        sleep(3);
        header('location:start.html');
        exit;
    } else {
        // 调用显示用户控制界面的函数
        showControlPanel($username);
        header('location:start.html');
        exit;
    }

    // 关闭数据库连接
    $conn->close();
} else {
    echo "未找到JWT Token，请重新登录！";
    sleep(3);
    header('location:start,html');
    exit;
}

// 显示用户控制界面的函数
function showControlPanel($username) {
    // 获取base64加密后的用户名
    $encoded_username = base64_encode($username);

    // 构建文件夹路径
    $folder_path = 'upload/' . $encoded_username;
    // 设置文件夹权限（例如，设置为 755）
    chmod($folder_path, 0755);
    // 检查当前文件夹的大小
    $currentFolderSize = round(folderSize($folder_path) / (1024 * 1024),4);

    // 检查文件夹是否存在
    if (!file_exists($folder_path)) {
        echo "文件夹不存在！";
    } else {
        // 输出HTML头部分
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件列表</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .file-list {
            list-style: none;
            padding: 0;
        }
        .file-item {
            margin-bottom: 10px;
        }
        .file-link {
            text-decoration: none;
            color: #007BFF;
            margin-right: 10px;
        }
        .file-actions {
            color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>文件列表</h1>
    <ul class="file-list">';

        // 遍历文件夹内的文件并输出
        $files = scandir($folder_path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                // 输出文件名及下载、删除链接
                $safe_file_name = basename($file); // 获取安全的文件名
                $decodefilename = urldecode($safe_file_name);
                $file_path = 'upload/' . $encoded_username.'/'.$safe_file_name;
                $filesize = round(filesize($file_path) / (1024 * 1024),4);
                echo '<li class="file-item">' . $decodefilename . ' - ' . $filesize . 'MB' . ' - <a class="file-link" href="DownloadFile.php?filename=' . $safe_file_name . '">下载</a> - <a class="file-actions" href="DeleteFile.php?filename=' . $safe_file_name . '">删除</a></li>';
            }
        }
        echo "已经使用了" . $currentFolderSize."MB，共200MB";

        // 输出HTML尾部分
        echo '</ul>
</div>

</body>
</html>';
include 'view/control_panel.html';
    }
}
?>
