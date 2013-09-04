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
            'App\I18n\Translator\Translator' => 'App\I18n\Translator\TranslatorServiceFactory',  
            'App\FileManager\FileManager' => 'App\FileManager\FileManagerFactory',
            'App\Method\MethodManager' => 'App\Method\MethodManagerFactory',    
            'Zend\Log\Logger' => 'Zend\Log\LoggerServiceFactory',    
            
            'App\View\Strategy\XmlStrategy' => 'App\Mvc\Service\ViewXmlStrategyFactory',            
            'App\View\Strategy\VarExportStrategy' => 'App\Mvc\Service\ViewVarExportStrategyFactory',    
            'App\View\Strategy\PrintRStrategy' => 'App\Mvc\Service\ViewPrintRStrategyFactory',    
        ),
        'invokables' => array(
            'App\ObjectType\ObjectType' => 'App\ObjectType\ObjectType',
            'App\ObjectType\Form' => 'App\ObjectType\Form',
            'App\ObjectPropertyCollection\ObjectPropertyCollection' => 'App\ObjectPropertyCollection\ObjectPropertyCollection',     
            'App\ObjectsCollection\ObjectsCollection' => 'App\ObjectsCollection\ObjectsCollection',
            'App\ObjectTypesCollection\ObjectTypesCollection' => 'App\ObjectTypesCollection\ObjectTypesCollection',   
            'App\FieldsCollection\FieldsCollection' => 'App\FieldsCollection\FieldsCollection',
            'App\FieldTypesCollection\FieldTypesCollection' => 'App\FieldTypesCollection\FieldTypesCollection',   
            'App\SqlParser\SqlParser' => 'App\SqlParser\SqlParser',
            'App\Log\Writer\ArrayWriter' => 'App\Log\Writer\ArrayWriter',
            'App\View\ResultComposer\JsonComposer' => 'App\View\ResultComposer\JsonComposer',
            'App\View\ResultComposer\XmlComposer' => 'App\View\ResultComposer\XmlComposer',
            'App\View\ResultComposer\VarExportComposer' => 'App\View\ResultComposer\VarExportComposer',
            'App\View\ResultComposer\PrintRComposer' => 'App\View\ResultComposer\PrintRComposer',
            
            'App\View\Renderer\XmlRenderer' => 'App\View\Renderer\XmlRenderer',
            'App\View\Renderer\VarExportRenderer' => 'App\View\Renderer\VarExportRenderer',
            'App\View\Renderer\PrintRRenderer' => 'App\View\Renderer\PrintRRenderer',
            
            'App\View\RendererStrategy' => 'App\View\RendererStrategy',
            
        ),
        'aliases' => array(
            'Translator' => 'App\I18n\Translator\Translator',
            'FileManager' => 'App\FileManager\FileManager',
            'MethodManager' => 'App\Method\MethodManager',
            'Logger' => 'Zend\Log\Logger',
            'ObjectType' => 'App\ObjectType\ObjectType',
            'ObjectTypeForm' => 'App\ObjectType\Form',
            'ObjectPropertyCollection' => 'App\ObjectPropertyCollection\ObjectPropertyCollection',
            'ObjectsCollection' => 'App\ObjectsCollection\ObjectsCollection',
            'ObjectTypesCollection' => 'App\ObjectTypesCollection\ObjectTypesCollection',
            'FieldsCollection' => 'App\FieldsCollection\FieldsCollection',
            'FieldTypesCollection' => 'App\FieldTypesCollection\FieldTypesCollection',
            'SqlParser' => 'App\SqlParser\SqlParser',
            'bugHunter' => 'App\Log\Writer\ArrayWriter',
        ),
        'shared' => array(
            'App\ObjectType\ObjectType' => false,
            'App\ObjectType\Form' => false,
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
                'base_dir' => ROOT_PATH . '/core/vendor/App/resources/languages',
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
        'not_found_template'       => 'page_template/core/error/404.phtml',
        'exception_template'       => 'page_template/core/error/index.phtml',
        'template_map' => array(
            'layout/layout'        => APPLICATION_PATH . '/view/wrapper/core/wrapper.phtml',
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
