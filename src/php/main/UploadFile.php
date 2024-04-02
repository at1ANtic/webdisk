<?php
error_reporting(0);
header("Content-type: text/html; charset=utf-8");
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

    // 解码JWT Token获取用户信息
    $token_parts = explode('.', $jwt_token);
    $token_payload = json_decode(base64_decode($token_parts[1]), true);
    $username = $token_payload['username'];

    // 创建base64加密后的用户名作为文件夹名称
    $encoded_username = base64_encode($username);
    $upload_path = 'upload/' . $encoded_username;

    // 检查JWT Token是否为数据库中的用户
    // （在实际应用中，应该使用更安全的方式，这里仅为演示）
    include 'config.php'; // 包含数据库连接配置文件

    // 创建数据库连接
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }

    // 预处理 SQL 查询
    $check_user_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $check_user_result = $stmt->get_result();

    if ($check_user_result->num_rows === 0) {
        // 如果用户不存在，输出错误信息并终止上传
        echo "用户不存在，请重新登录！";
        exit();
    }

    // 输出 HTML 头部
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
    </head>';

    // 输出文件上传表单
    echo '<div class="container">
        <body>
            <h1>文件上传</h1>
            <form action="UploadFile.php" method="post" enctype="multipart/form-data">
                请选择您要上传的文件：<input type="file" name="myFile" /><br/>
                <input type="submit" value="上传"/>
            </form>
        </body>
    </html>';

    include 'view/control_panel.html';

    // 其他上传文件的代码...
    $imgname = $_FILES['myFile']['name'];
    $tmp = $_FILES['myFile']['tmp_name'];
    $error = $_FILES['myFile']['error'];
    $flag = 0;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 如果接收到POST请求，将flag设置为1
        $flag = 1;
        // 设置文件夹权限（例如，设置为 755）
        chmod($upload_path, 0755);
        // 限制用户文件夹的大小为200MB
        $maxFolderSizeMB = 200; // 设置最大文件夹大小，单位为兆字节
        // 检查当前文件夹的大小
        $currentFolderSize = folderSize($upload_path);
        // 检查当前文件夹大小是否超过允许的最大大小
        if ($currentFolderSize > ($maxFolderSizeMB * 1024 * 1024)) {
            $error = 2;
        }
    }
    if($imgname==NULL)
    {
        $error = 1;
    }

    // 移动上传文件到指定路径
    if ($error == 0 && $flag == 1) {
        // 使用 mb_convert_encoding 将文件名从 UTF-8 转换为 GBK
        $encoded_filename = urlencode($imgname);
    
    // 将上传文件移动到指定路径
    move_uploaded_file($tmp, $upload_path . '/' . $encoded_filename);
        echo "上传成功！";
        header("Location: control.php");
        exit(); // 重要：确保在跳转后终止脚本执行
    } else {
        switch ($error) {
            case 1:
                echo "未选择上传文件！";
                break;
            case 2:
                echo "用户文件夹大小超过了 {$maxFolderSizeMB} MB 的最大限制。";
        }
    }

    // 关闭数据库连接
    $stmt->close();
    $conn->close();
} else {
    echo "未找到JWT Token，请重新登录！";
}
?>
