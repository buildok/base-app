<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

require_once __DIR__.'/vendor/autoload.php';

define('ROOT', dirname(__FILE__));
// define('CONFIG_PATH', ROOT . '/config/web.php');

(new buildok\base\Application())->run();
