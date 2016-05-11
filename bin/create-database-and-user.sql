create database if not exists developer_portal default character set=utf8 collate=utf8_general_ci;

grant all on `developer_portal`.* to `developer_portal`@`localhost` identified by 'PASSWORD_HERE';

flush privileges;
