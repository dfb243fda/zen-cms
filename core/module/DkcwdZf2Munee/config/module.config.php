<?php
return array(
    'DkcwdZf2Munee' => array(
        'title' => 'Адаптер для Munee',
        'description' => 'dsfsdf',
        
        'version' => '0.1',
    ),
    'router' => array(
        'routes' => array(
            'DkcwdZf2Munee' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/munee',
                    'defaults' => array(
                        'controller' => 'DkcwdZf2Munee\Controllers\Munee',
                        'action'     => 'munee',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'DkcwdZf2Munee\Controllers\Munee' => 'DkcwdZf2Munee\Controllers\MuneeController',
        ),
    ),
);
