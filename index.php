<?php

// start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// project paths
define('BASE_PATH', __DIR__);
define('BASE_URL', '/bookstore/');

// include core files
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/App.php';

// include helper files
require_once BASE_PATH . '/app/helpers/auth.php';
require_once BASE_PATH . '/app/helpers/functions.php';

// run the app
$app = new App();
$app->run();