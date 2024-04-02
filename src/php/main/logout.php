<?php

// 删除本地存储的JWT Token（模拟使用Cookie）
setcookie('jwt_token', '', time() - 3600, '/');

// 转跳到登录界面
header('Location: start.html');
exit();

?>
