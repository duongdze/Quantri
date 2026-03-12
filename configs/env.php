<?php

define('BASE_URL', 'http://localhost/Duan1/');
define('BASE_URL_ADMIN', 'http://localhost/Duan1/?mode=admin');

define('PATH_ROOT',                 __DIR__ . '/../');

define('PATH_VIEW_ADMIN',           PATH_ROOT . 'views/admin/');
define('PATH_VIEW_CLIENT',          PATH_ROOT . 'views/client/');

define('PATH_VIEW_ADMIN_MAIN',           PATH_ROOT . 'views/admin/main.php');
define('PATH_VIEW_CLIENT_MAIN',          PATH_ROOT . 'views/client/main.php');

define('BASE_ASSETS_ADMIN',         BASE_URL . 'assets/admin/');
define('BASE_ASSETS_CLIENT',        BASE_URL . 'assets/client/');
define('BASE_ASSETS_UPLOADS',       BASE_URL . 'assets/uploads/');

define('PATH_ASSETS_UPLOADS',       PATH_ROOT . 'assets/uploads/');

define('PATH_CONTROLLER_ADMIN',     PATH_ROOT . 'controller/admin/');
define('PATH_CONTROLLER_CLIENT',    PATH_ROOT . 'controller/client/');

define('PATH_MODEL',                PATH_ROOT . 'models/');

define('DB_HOST',           'localhost');
define('DB_PORT',           '3306');
define('DB_USERNAME',       'root');
define('DB_PASSWORD',       '');
define('DB_NAME',           'pro1014');
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
