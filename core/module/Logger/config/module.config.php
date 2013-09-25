<?php

return array(
    'Logger' => array(
        'title' => 'i18n::Logger module',
        'description' => 'i18n::Logger module description',
        'version' => '0.1',
        
        'priority' => -10,
        'isRequired' => true,
        
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
    ), 
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'Logger\Service\Logger' => 'Logger\Service\Logger',
        ),
    ),
);
