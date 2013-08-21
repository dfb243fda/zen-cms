<?php

return array(
    'Modules' => array(
        'title' => 'i18n::Modules module',
        'description' => 'i18n::Modules module description',
        'version' => '0.1',
        
        'priority' => -10,
        'isRequired' => true,
        
        'permission_resources' => array(
            array(
                'resource' => 'be_method_access',
                'privelege' => '',
                'name' => 'i18n::All be methods access',
            ),
        ),
        
        'methods' => array(            
            'ModulesList' => array(
                'service' => 'Modules\Method\ModulesList',
                'title' => 'i18n::Modules list method',
                'description' => 'i18n::Modules list method description',
                'type' => 'be',
                'menu_group' => 'moduleManager',
            ),
            'ModuleInfo' => array(
                'service' => 'Modules\Method\ModuleInfo',
                'title' => 'i18n::Module info method',
                'description' => 'i18n::Module info method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ModulesList',
            ),
            'InstallModule' => array(
                'service' => 'Modules\Method\InstallModule',
                'title' => 'i18n::Install module method',
                'description' => 'i18n::Install module method description',
                'type' => 'be',
            ),
            'UninstallModule' => array(
                'service' => 'Modules\Method\UninstallModule',
                'title' => 'i18n::Uninstall module method',
                'description' => 'i18n::Uninstall module method description',
                'type' => 'be',
            ),
            'ActivateModule' => array(
                'service' => 'Modules\Method\ActivateModule',
                'title' => 'i18n::Activate module method',
                'description' => 'i18n::Activate module method description',
                'type' => 'be',
            ),
            'DeactivateModule' => array(
                'service' => 'Modules\Method\DeactivateModule',
                'title' => 'i18n::Deactivate module method',
                'description' => 'i18n::Deactivate module method description',
                'type' => 'be',
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
        'moduleManager' => array(
            'title' => 'i18n::Module manager menu group',
        ),
    ),
);
