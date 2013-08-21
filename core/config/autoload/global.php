<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(    
    'service_manager' => array(
        'factories' => array(
            'translator' => 'App\I18n\Translator\TranslatorServiceFactory',  
            'fileManager' => 'App\FileManager\FileManagerFactory',
            'methodManager' => 'App\Method\MethodManagerFactory',    
            'logger' => 'Zend\Log\LoggerServiceFactory',
        ),
        'invokables' => array(
            'objectPropertyCollection' => 'App\ObjectPropertyCollection\ObjectPropertyCollection',     
            'objectsCollection' => 'App\ObjectsCollection\ObjectsCollection',
            'objectTypesCollection' => 'App\ObjectTypesCollection\ObjectTypesCollection',   
            'fieldsCollection' => 'App\FieldsCollection\FieldsCollection',
            'fieldTypesCollection' => 'App\FieldTypesCollection\FieldTypesCollection',   
            'sqlParser' => 'App\SqlParser\SqlParser',
            'bugHunter' => 'App\Log\Writer\ArrayWriter',
        ),
    ),
    'translator' => array(
        'locale' => 'ru_RU',
        'translation_file_patterns' => array(
            array(                
                'type'     => 'phparray',
                'base_dir' => CORE_PATH . '/language',
                'pattern'  => '%s.php',
            ),
            array(
                'type'     => 'phparray',
                'base_dir' => ROOT_PATH . '/core/vendor/ZF2/resources/languages',
                'pattern'  => '%s/Zend_Validate.php',
            ),
        ),
    ),
    'log' => array(
        'exceptionhandler' => false,
        'errorhandler' => false,
        'writers' => array(
            array(
                'name' => 'Zend\Log\Writer\Null'
            ),
//            array(
//                'name' => 'Zend\Log\Writer\FirePhp'
//            ),
        ),
    ),    
    'log_writers' => array(
        'db' => array(
            'priority' => 10,
            'options' => array(
                'column' => array(
                    'timestamp' => 'timestamp',
                    'priority' => 'type',
                    'message' => 'message',            
                    'extra' => array(
                        'line' => 'line',
                        'errno' => 'errno',
                        'file' => 'file',
                        'class' => 'class',
                        'function' => 'function',
                        'uri' => 'uri',
                        'client_ip' => 'client_ip',
                        'user_id' => 'user_id',
                    ),
                ),
                'table' => 'logs',
            ),
        ),
        'bugHunter' => array(
            'priority' => -1,
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'        => APPLICATION_PATH . '/view/wrapper/core/wrapper.phtml',
            'error/404'            => APPLICATION_PATH . '/view/page_template/core/error/404.phtml',
            'error/index'          => APPLICATION_PATH . '/view/page_template/core/error/index.phtml',
        ),
        'template_path_stack' => array(
            APPLICATION_PATH . '/view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'translateI18n' => 'App\I18n\View\Helper\TranslateI18n',
            'formElementWrapper' => 'App\Form\View\Helper\FormElementWrapper',
            'formElementWrapper3C' => 'App\Form\View\Helper\FormElementWrapper3C',
        ),
    ),
);
