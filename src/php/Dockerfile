FROM php:7.3-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN sed -i "s@http://\(deb\|security\).debian.org@https://mirrors.aliyun.com@g" /etc/apt/sources.list
RUN apt-get update && apt-get upgrade -y
COPY ./main /var/www/html/
RUN chmod 0777 /var/www/html/upload
RUN chmod 0777 /var/www/html/
RUN chmod +x /var/www/html/UploadFile.php
RUN chmod +x /var/www/html/DownloadFile.php
RUN echo "<Directory /var/www/html/upload>\n  php_flag engine off\n</Directory>" > /etc/apache2/conf-enabled/upload.conf
RUN service apache2 restart