<?php
require_once('../config.php');

Config::setPath(dirname(__FILE__) . '/config');
Config::load('db');

printf("db charset: %s\n", Config::get('db.charset'));
printf("db host: %s\n", Config::get('db.default.host'));
printf("db user: %s\n", Config::get('db.default.user'));
printf("db pass: %s\n", Config::get('db.default.pass'));
printf("db port: %s\n", Config::get('db.default.port'));
printf("db name: %s\n", Config::get('db.default.dbname'));

printf("--------------------------------\n");

printf("site title: %s\n", Config::get('site.title'));
printf("site description: %s\n", Config::get('site.description'));
printf("site keyword: %s\n", Config::get('site.keyword'));
printf("site menu: %s\n", implode(",", Config::get('site.menu')));
