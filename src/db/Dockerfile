FROM mysql:5.7

ENV MYSQL_ROOT_PASSWORD MYSQL_ROOT_PASSWORD
ENV MYSQL_DATABASE MYSQL_DATABASE
ENV MYSQL_USER MYSQL_USER
ENV MYSQL_PASSWORD MYSQL_PASSWORD

COPY ./users.sql /docker-entrypoint-initdb.d/users.sql
RUN chmod +x /docker-entrypoint-initdb.d/users.sql