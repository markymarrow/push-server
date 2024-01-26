#!/usr/bin/env php
<?php

require "/app/vendor/autoload.php";

spl_autoload_register(function ($class) {
    $class_path = str_replace('\\', '/', $class);
    $file = __DIR__ . "/{$class_path}.php";
    if (file_exists($file)) {
        require $file;
    }
});

use PushServer\Application;
use PushServer\Notification;

// initialise database link
$pdoDatabase = new PDO(
    "mysql:host=" . getenv('DB_HOSTNAME') . ";dbname=" . getenv('MYSQL_DATABASE') . ";charset=utf8mb4",
    getenv('MYSQL_USER'),
    getenv('MYSQL_PASSWORD')
);

$app = new Application('com.test.app');
$notification = new Notification('Hello', 'World');

$app->sendToAllDevices($notification);