<?php

return array(
    'Templates' => array(
        'title' => 'i18n::Templates module',
        'description' => 'i18n::Templates module description',
        'version' => '0.1',
        'methods' => array(
            'TemplatesList' => array(
                'service' => 'Templates\Method\TemplatesList',
                'title' => 'i18n::Templates list method',
                'description' => 'i18n::Templates list method description',
                'menu_group' => 'templates',
                'type' => 'be',
            ),
            'AddTemplate' => array(
                'service' => 'Templates\Method\AddTemplate',
                'title' => 'i18n::Add template method',
                'description' => 'i18n::Add template method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'TemplatesList',
            ),
            'EditTemplate' => array(
                'service' => 'Templates\Method\EditTemplate',
                'title' => 'i18n::Edit template method',
                'description' => 'i18n::Edit template method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'TemplatesList',
            ),
            'DeleteTemplate' => array(
                'service' => 'Templates\Method\DeleteTemplate',
                'title' => 'i18n::Delete template method',
                'description' => 'i18n::Delete template method description',
                'type' => 'be',
            ),
        ),
        'templatesDir' => 'view',
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'Templates\Listener\ModuleTemplatesCollector' => 'Templates\Listener\ModuleTemplatesCollector',
            'Templates\Service\Installer' => 'Templates\Service\Installer',
            'Templates\Service\TemplatesList' => 'Templates\Service\TemplatesList',
            'Templates\FormFactory\TemplatesFormFactory' => 'Templates\FormFactory\TemplatesFormFactory',
            'Templates\Entity\TemplateEntity' => 'Templates\Entity\TemplateEntity',
            'Templates\Form\TemplateForm' => 'Templates\Form\TemplateForm',
            'Templates\Form\TemplateWithMarkersForm' => 'Templates\Form\TemplateWithMarkersForm',
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
    'menu_groups' => array(
        'templates' => array(
            'title' => 'i18n::Templates menu group',
        ),
    ),
    
    'router' =>array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'TemplatesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Templates/TemplatesList[/module_:templateModule{/.}][/method_:templateMethod{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Templates',
                                'method' => 'TemplatesList',
                            ),
                        ),
                    ),
                    'AddTemplate' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Templates/AddTemplate[/module_:templateModule{/.}][/method_:templateMethod{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Templates',
                                'method' => 'AddTemplate',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);