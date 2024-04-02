# 基于PHP的网盘系统

预览项目地址[Atlantic web disk](http://8.130.42.53/)

dockerhub分为了两个镜像（php和db）

[atlant1c/disk-db general | Docker Hub](https://hub.docker.com/repository/docker/atlant1c/disk-db/general)

[atlant1c/disk-php general | Docker Hub](https://hub.docker.com/repository/docker/atlant1c/disk-php/general)

## 基本信息

**实现的功能：**

1. 注册（为每个新用户创建新的文件夹）。
2. 基于JWT认证实现登陆，以及分别存放上传的文件。
3. 注销用户（删字段，删文件夹）
4. 用户的控制面板，可视化文件以及大小。
5. 限制每个用户的网盘大小。
6. 文件上传，下载，删除。
7. 前端UI（搬运b站up@山羊の前端小窝）
8. docker一键部署(配置php-apache环境，MySQL初始化)。

**关于安全方面的考虑：**

1. 关闭报错防止泄露信息
2. 使用预处理防止sql注入
3. 不可逆加密用户密码，防止因为sql注入而泄露密码
4. 禁用存放文件的目录执行PHP脚本，防止上传木马文件（如果可以任意目录上传文件，那请您随便传马吧）
5. 存放文件的目录以及文件名进行了可逆的加密，一定程度上加大了寻找文件目录的难度（对文件名进行url加密可以防止带有中文而造成的乱码）

**一个有趣的功能**

bing每日壁纸会每天更新一张质量不错的壁纸，结合官方给出的API，用php脚本实现获取图片的URL。将该图片来装饰主页。（可以当作博客背景，当然你的博客要有php环境）

![image-20240127225455406](https://f1gure-bed.obs.cn-southwest-2.myhuaweicloud.com/image-20240127225455406.png)

bingpicture.php

```php
<?php
$jsonUrl = 'https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=zh-CN';
// 使用 file_get_contents 获取链接的源代码
$jsonData = file_get_contents($jsonUrl);
// 将 JSON 数据解码为关联数组
$data = json_decode($jsonData, true);
// 获取 images 数组中的第一个元素的 url 值
$url = $data['images'][0]['url'];
$bingHomepage = 'https://cn.bing.com/';
// 结合链接并进行跳转
header("Location: $bingHomepage$url");
exit();
?>
```

## 部署方式

进入disk文件夹，`docker compose up -d`

端口8000

## 文件和目录结构

images是图片。

upload则是上传的总目录。

view放了一个html，用户的控制面板以及文件上传界面都包含了该文件。

vender是composers中JWT库的一些文件

然后就是核心的文件了，index.html页面引导进入start.html界面，实现了同一个页面进行登陆和注册功能（分别发送请求到两个php脚本实现与数据库交互）

登陆进去后就是control.php用户的控制面板，里面的左侧边有着为数不多的功能。

要慎用注销用户功能，因为用完啥也不胜了。

## 开发过程中遇到的苦难以及收获

**困难**

连接数据库的php函数，网上的源码都比较老，有些还在使用如下等函数进行连接数据库。![QQ图片20240127223250](https://f1gure-bed.obs.cn-southwest-2.myhuaweicloud.com/QQ%E5%9B%BE%E7%89%8720240127223250.png)

docker配置环境花了很久的时间，出现了很多的问题，总是解决了一个问题，后面还有新的问题在等我。

连接数据库并且初始化（创建库，导入表），php脚本创建文件夹，下载文件中访问并输出文件二进制流。都是涉及权限的设置。

**收获**

php扩展（mysqli库面向对象）。

对于身份认证（JWT，session）有了更深入的了解，本系统为了更加容易认证而选择了JWT。

php与数据库的共同使用来实现一些功能（注册，登陆）。

docker-compose.yml与Dockerfile搭配使用来实现镜像的生成以及运行容器。太懒了没去学卷来实现数据持久化。放个链接日后来学[Docker——数据卷的概述和使用_数据卷的作用-CSDN博客](https://blog.csdn.net/wpc2018/article/details/121634538)

在Dockerfile运行Linux命令又练习了和学习了操作系统的知识，比如为文件夹和文件添加权限，换源等

了解学到了php预处理处理sql语句来防止sql注入。
