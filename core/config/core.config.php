<?php
return call_user_func(function() {    
    defined('DS') || define('DS', DIRECTORY_SEPARATOR);
    defined('TIME_START') || define('TIME_START', microtime(true));
    defined('ROOT_PATH') || define('ROOT_PATH', dirname(dirname(__DIR__)));
        
    defined('CORE_PATH') || define('CORE_PATH', dirname(__DIR__));
    defined('PUBLIC_PATH') || define('PUBLIC_PATH', getcwd());
    
    defined('APPLICATION') || define('APPLICATION', basename(PUBLIC_PATH));
    defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_PATH . DS . 'application' . DS . APPLICATION);
    
    defined('SERVER_OS') || define('SERVER_OS', stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin') ? 'WIN' : '');
    // a tabulator
    defined('TAB') || define('TAB', chr(9));
    // a linefeed
    defined('LF') || define('LF', chr(10));
    // a carriage return
    defined('CR') || define('CR', chr(13));
    // a CR-LF combination
    defined('CRLF') || define('CRLF', CR . LF);

    $config = array();
    
    if ('production' == APPLICATION_ENV) {
        $config['show_errors_to_everybody'] = false;
        $config['phpSettings'] = array(
            'display_startup_errors' => 0,
            'display_errors' => 0,
            'error_reporting' => -1,
        );
    }
    elseif ('development' == APPLICATION_ENV) {
        $config['show_errors_to_everybody'] = true;
        $config['phpSettings'] = array(
            'display_startup_errors' => 1,
            'display_errors' => 1,
            'error_reporting' => -1,
        );
    }
    else {
        exit('unknown constant APPLICATION_ENV == ' . APPLICATION_ENV . '<br />It must be "development" or "production"');
    }
    
    $config['phpSettings']['date.timezone'] = 'Europe/Moscow';

    $config['chmod_dir'] = '0777';
    $config['chmod_file'] = '0666';
    $config['create_group'] = false;

    $config['locale'] = 'ru_RU.UTF-8';
    $config['language'] = 'ru_RU';
    $config['dbPref'] = '';
    $config['dbCharset'] = 'UTF8';
    
    $config['path']['root'] = ROOT_PATH;


    /* Пути для ядра */
    $config['path']['core'] = CORE_PATH;
    $config['path']['core_config'] = __DIR__;
    $config['path']['core_lib'] = $config['path']['core'] . DS . 'library';
    $config['path']['core_ext'] = $config['path']['core'] . DS . 'module';
    $config['path']['core_language'] = $config['path']['core'] . DS . 'language';
    $config['path']['core_view'] = $config['path']['core'] . DS . 'view';
    $config['path']['core_public_resources'] = $config['path']['core'] . DS . 'public';
    /* Пути для ядра */


    /* Пути для текущего сайта */
    $config['path']['application'] = APPLICATION_PATH;

    $config['path']['application_config'] = $config['path']['application'] . DS . 'config';
    $config['path']['application_lib'] = $config['path']['application'] . DS . 'library';
    $config['path']['application_ext'] = $config['path']['application'] . DS . 'module';
    $config['path']['application_language'] = $config['path']['application'] . DS . 'language';
    $config['path']['application_view'] = $config['path']['application'] . DS . 'view';
    $config['path']['application_temp'] = $config['path']['application'] . DS . 'temp';
    $config['path']['application_files'] = $config['path']['application'] . DS . 'files';

    /* Пути для текущего сайта */


    /* Публичные пути */
    $config['path']['public'] = PUBLIC_PATH;

    $config['path']['public_img'] = $config['path']['public'] . DS . 'img';
    $config['path']['public_js'] = $config['path']['public'] . DS . 'js';
    $config['path']['public_css'] = $config['path']['public'] . DS . 'css';
    $config['path']['public_uploads'] = $config['path']['public'] . DS . 'uploads';
    $config['path']['public_temp'] = $config['path']['public'] . DS . 'temp';
    $config['path']['public_files'] = $config['path']['public'] . DS . 'files';

    $config['path']['public_core_js'] = $config['path']['public_js'] . DS . 'core';
    $config['path']['public_core_css'] = $config['path']['public_css'] . DS . 'core';
    $config['path']['public_core_img'] = $config['path']['public_img'] . DS . 'core';

    $config['path']['public_application_js'] = $config['path']['public_js'] . DS . 'application';
    $config['path']['public_application_css'] = $config['path']['public_css'] . DS . 'application';
    $config['path']['public_application_img'] = $config['path']['public_img'] . DS . 'application';

    /* Публичные пути */
    
    
    $config['module_listener_options'] = array(
        'module_paths' => array(
            CORE_PATH . '/module',
            APPLICATION_PATH . '/module',
        ),
        'config_glob_paths' => array(
            CORE_PATH . '/config/autoload/{,*.}{global,local}.php',
            APPLICATION_PATH . '/config/autoload/{,*.}{global,local}.php',
        ),
        'config_cache_enabled' => false,
        'config_cache_key' => APPLICATION,
        'module_map_cache_enabled' => false,
        'module_map_cache_key' => APPLICATION,
        'cache_dir' => APPLICATION_PATH . '/cache',
    );
    
    $config['service_manager'] = array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'App\Db\Adapter\AdapterServiceFactory',
            'ModuleManager' => 'App\Mvc\Service\ModuleManagerFactory',
        ),        
        'aliases' => array(
            'db' => 'Zend\Db\Adapter\Adapter',
        ),
    );
    
    $config['db'] = array(
        'driver' => 'Pdo',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
        ),
        'profiler' => true,
    );    
    
    return $config;
});