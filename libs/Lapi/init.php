<?php

/**
 * Load stuff
 */

require_once('helpers.php');
require_once('CoreClasses/LapiApplication.php');


spl_autoload_register(function ($class) {
    require_once('CoreClasses/' . $class . '.php');
});



/**
 * Start app
 */


$app = new LapiApplication();
$GLOBALS['app'] = $app;

$app->user = new LapiUser();
