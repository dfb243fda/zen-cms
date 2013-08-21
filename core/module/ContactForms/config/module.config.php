<?php

return array(
    'ContactForms' => array(
        'title' => 'i18n::Contact forms module',
        'description' => 'i18n::Contact forms module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::ContactForms:Contact form template',
                'name' => 'contact_form.phtml',
                'type' => 'content_template',
                'method' => 'SingleForm',
            ),
        ),
        
        'methods' => array(
            'FormsList' => array(
                'service' => 'ContactForms\Method\FormsList',
                'title' => 'i18n::Forms list method',
                'description' => 'i18n::Forms list method description',
                'type' => 'be',
                'menu_group' => 'contact_forms',
            ),
            'AddForm' => array(
                'service' => 'ContactForms\Method\AddForm',
                'title' => 'i18n::Add form method',
                'description' => 'i18n::Add form method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'FormsList',
            ),
            'EditForm' => array(
                'service' => 'ContactForms\Method\EditForm',
                'title' => 'i18n::Edit form method',
                'description' => 'i18n::Edit form method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'FormsList',
            ),
            'DelForm' => array(
                'service' => 'ContactForms\Method\DelForm',
                'title' => 'i18n::Delete form method',
                'description' => 'i18n::Delete form method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'FormsList',
            ),
            'SingleForm' => array(
                'service' => 'ContactForms\Method\SingleForm',
                'title' => 'i18n::Single form method',
                'description' => 'i18n::Single form method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
                'directAccess' => true,
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
    'menu_groups' => array(
        'contact_forms' => array(
            'title' => 'i18n::Contact forms menu group',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'FormsList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'ContactForms/FormsList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'ContactForms',
                                'method' => 'FormsList',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);