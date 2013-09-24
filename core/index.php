<?php

if (version_compare(PHP_VERSION, '5.3.1', '<')) {
	exit('Your host needs to use PHP 5.3.1 or higher to run this version of Zen-CMS!');
}

if (!defined('APPLICATION')) {
    exit('constant APPLICATION does not defined (use define(\'APPLICATION\', \'some_name\'))');
}

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Setup autoloading
require __DIR__ . '/init_autoloader.php';

$tmpConfig = require __DIR__ . '/config/core.config.php';

if (file_exists(APPLICATION_PATH  . '/config/application.config.php')) {
    $tmpFunc = require APPLICATION_PATH  . '/config/application.config.php';    
    $tmpFunc($tmpConfig);
    unset($tmpFunc);
}
if (file_exists(APPLICATION_PATH  . '/config/application.config.local.php')) {
    $tmpFunc = require APPLICATION_PATH  . '/config/application.config.local.php';    
    $tmpFunc($tmpConfig);
    unset($tmpFunc);
}

// Run the application!
$_APPLICATION = Zend\Mvc\Application::init($tmpConfig);
unset($tmpConfig);
$_APPLICATION->run();