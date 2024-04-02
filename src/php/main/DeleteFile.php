<?php
error_reporting(0);

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

$jwt_token = isset($_COOKIE['jwt_token']) ? $_COOKIE['jwt_token'] : null;

if (!$jwt_token) {
    echo json_encode(['error' => '未提供JWT Token']);
    exit;
}

$token_parts = explode('.', $jwt_token);
$token_payload = json_decode(base64_decode($token_parts[1]), true);
$username = $token_payload['username'];

$filename = isset($_GET['filename']) ? $_GET['filename'] : null;

if (!$filename) {
    echo json_encode(['error' => '未提供要删除的文件名']);
    exit;
}

include 'config.php';

$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    echo json_encode(['error' => '数据库连接失败: ' . $conn->connect_error]);
    exit;
}

$check_user_query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($check_user_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$check_user_result = $stmt->get_result();

if ($check_user_result->num_rows === 0) {
    echo json_encode(['error' => '用户不存在，请重新登录']);
    exit;
}

$encoded_username = base64_encode($username);
$encoded_filename = urlencode($filename);
$file_path = 'upload/' . $encoded_username . '/' . $encoded_filename;

if (!empty($file_path)) {
    if (is_file($file_path)) {
        if (unlink($file_path)) {
            $delete_result = '文件删除成功';
        } else {
            $delete_result = '无法删除文件';
        }
    } else {
        if (deleteDirectory($file_path)&&$file_path!='upload/' . $encoded_username . '/') {
            $delete_result = '文件夹删除成功';
        } else {
            $delete_result = '无法删除文件夹';
        }
    }
} else {
    $delete_result = '未提供有效的文件或文件夹路径';
}

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
<div class="container">';
echo $delete_result;
echo '</ul>
</div>

</body>
</html>';

$stmt->close();
$conn->close();

function is_dirEmpty($dir)
{
    $handle = opendir($dir);
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            closedir($handle);
            return false;
        }
    }
    closedir($handle);
    return true;
}

include 'view/control_panel.html';
?>
