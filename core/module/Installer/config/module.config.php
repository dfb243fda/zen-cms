<?php

return array(
    'Installer' => array(
        'title' => 'i18n::Installer module',
        'description' => 'i18n::Installer module description',
        'version' => '0.1',
        
        'demoSites' => array(
            '' => array(
                'title' => 'i18n::Installer demo site none',
            ),
            'demo1' => array(
                'title' => 'i18n::Installer demo site1',
                'image' => '/img/Installer/demosite1.gif',
                'service' => 'Installer\Demo\Demo1',
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
    'router' => array(
        'routes' => array(
            'install' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/install[/:action][/]',
                    'defaults' => array(
                        'controller' => 'Installer\Controller\Install',
                        'action'     => 'step1',
                    ),
                ),
            ),
        ),
    ),  
    'service_manager' => array(
        'invokables' => array(
            'Installer\Service\Bootstrapper' => 'Installer\Service\Bootstrapper',
            'Installer\Service\InstallerResources' => 'Installer\Service\InstallerResources',
            'Installer\Form\LanguageForm' => 'Installer\Form\LanguageForm',
            'Installer\Form\DbSettingsForm' => 'Installer\Form\DbSettingsForm',
            'Installer\Service\CheckServer' => 'Installer\Service\CheckServer',
            'Installer\Service\CmsInstaller' => 'Installer\Service\CmsInstaller',
            'Installer\Service\DemoSites' => 'Installer\Service\DemoSites',
            'Installer\Form\AccessForm' => 'Installer\Form\AccessForm',
            'Installer\Service\ConfigFileCreator' => 'Installer\Service\ConfigFileCreator',
            
            'Installer\Model\Installer' => 'Installer\Model\Installer',
            'Installer\Demo\Demo1' => 'Installer\Demo\Demo1',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Installer\Controller\Install' => 'Installer\Controller\InstallController',
        ),
    ), 
    'view_manager' => array(
        'template_map' => array(
            'installer/install/step1' => APPLICATION_PATH . '/view/page_template/Installer/step1.phtml',
            'installer/install/step2' => APPLICATION_PATH . '/view/page_template/Installer/step2.phtml',
            'installer/install/step3' => APPLICATION_PATH . '/view/page_template/Installer/step3.phtml',
            'installer/install/step4' => APPLICATION_PATH . '/view/page_template/Installer/step4.phtml',
            'installer/install/step5' => APPLICATION_PATH . '/view/page_template/Installer/step5.phtml',
            'installer/install/step6' => APPLICATION_PATH . '/view/page_template/Installer/step6.phtml',
            'installer/install/complete' => APPLICATION_PATH . '/view/page_template/Installer/complete.phtml',
        ),
    ),    
);