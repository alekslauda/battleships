<?php

use Application\Controllers\Application as Application;

define("APP_DIR", __DIR__.DIRECTORY_SEPARATOR."Application");
define("VIEWS", APP_DIR.DIRECTORY_SEPARATOR."Views");
define("CONTROLLERS", APP_DIR.DIRECTORY_SEPARATOR."Controllers");

require_once __DIR__.'/Helpers/defines.php';

error_reporting(-1);
ini_set('display_errors', 'On');

function autoloader($class) {
    $filepath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . ".php";
    require_once $filepath;
}

spl_autoload_register('autoloader');


$app = new Application();