<?php

return array(
    'Menu' => array(
        'title' => 'i18n::Menu module',
        'description' => 'i18n::Menu module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::Menu:Menu template',
                'name' => 'menu.phtml',
                'type' => 'content_template',
                'method' => 'SingleMenu',
            ),
        ),
        
        'methods' => array(
            'MenuList' => array(
                'service' => 'Menu\Method\MenuList',
                'title' => 'i18n::Menu list method',
                'description' => 'i18n::Menu list method description',
                'type' => 'be',
                'menu_group' => 'menu',
            ),
            'Edit' => array(
                'service' => 'Menu\Method\Edit',
                'title' => 'i18n::Menu edit method',
                'description' => 'i18n::Menu edit method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'AddMenu' => array(
                'service' => 'Menu\Method\AddMenu',
                'title' => 'i18n::Add menu method',
                'description' => 'i18n::Add menu method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'AddMenuItem' => array(
                'service' => 'Menu\Method\AddMenuItem',
                'title' => 'i18n::Add menu item method',
                'description' => 'i18n::Add menu item method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'Delete' => array(
                'service' => 'Menu\Method\Delete',
                'title' => 'i18n::Menu delete method',
                'description' => 'i18n::Menu delete method description',
                'type' => 'be',
            ),
            'SingleMenu' => array(
                'service' => 'Menu\Method\SingleMenu',
                'title' => 'i18n::Menu:Single menu method',
                'description' => 'i18n::Menu:Single menu method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'Menu\Service\Installer' => 'Menu\Service\Installer',
            'Menu\Service\MenuTree' => 'Menu\Service\MenuTree',
            'Menu\Service\Menu' => 'Menu\Service\Menu',
            'Menu\Collection\MenuCollection' => 'Menu\Collection\MenuCollection',
            
            'Menu\FormFactory\MenuFormFactory' => 'Menu\FormFactory\MenuFormFactory',
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
        'menu' => array(
            'title' => 'i18n::Menu menu group',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'MenuList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/MenuList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Menu',
                                'method' => 'MenuList',
                            ),
                        ),
                    ),
                    'AddMenu' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/AddMenu[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Menu',
                                'method' => 'AddMenu',
                            ),
                        ),
                    ),
                    'EditMenu' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/EditMenu[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Menu',
                                'method' => 'Edit',
                            ),
                        ),
                    ),
                    'AddMenuItem' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/AddMenuItem[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Menu',
                                'method' => 'AddMenuItem',
                            ),
                        ),
                    )
                ),
            ),
        ),
    ),    
);