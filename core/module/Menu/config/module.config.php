<?php

return array(
    'Menu' => array(
        'title' => 'i18n::Menu module',
        'description' => 'i18n::Menu module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::Menu:Menu tagCloud template',
                'name' => 'tagCloud.phtml',
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
            'EditMenu' => array(
                'service' => 'Menu\Method\EditMenu',
                'title' => 'i18n::Menu:EditMenu method',
                'description' => 'i18n::Menu:EditMenu method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'EditMenuItem' => array(
                'service' => 'Menu\Method\EditMenuItem',
                'title' => 'i18n::Menu:EditMenuItem method',
                'description' => 'i18n::Menu:EditMenuItem method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'AddMenu' => array(
                'service' => 'Menu\Method\AddMenu',
                'title' => 'i18n::Menu:AddMenu method',
                'description' => 'i18n::Menu:AddMenuu method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'AddMenuItem' => array(
                'service' => 'Menu\Method\AddMenuItem',
                'title' => 'i18n::Menu:AddMenuItem method',
                'description' => 'i18n::Menu:AddMenuItem method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'MenuList',
            ),
            'DeleteMenu' => array(
                'service' => 'Menu\Method\DeleteMenu',
                'title' => 'i18n::Menu:DeleteMenu method',
                'description' => 'i18n::Menu:DeleteMenu method description',
                'type' => 'be',
            ),
            'DeleteMenuItem' => array(
                'service' => 'Menu\Method\DeleteMenuItem',
                'title' => 'i18n::Menu:DeleteMenuItem method',
                'description' => 'i18n::Menu:DeleteMenuItem method description',
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
            'Menu\Collection\MenuItemsCollection' => 'Menu\Collection\MenuItemsCollection',
            
            'Menu\FormFactory\MenuFormFactory' => 'Menu\FormFactory\MenuFormFactory',
            'Menu\FormFactory\MenuItemFormFactory' => 'Menu\FormFactory\MenuItemFormFactory',
            
            'Menu\Entity\MenuEntity' => 'Menu\Entity\MenuEntity',
            'Menu\Entity\MenuItemEntity' => 'Menu\Entity\MenuItemEntity',
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
                    'EditMenu' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/EditMenu[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Menu',
                                'method' => 'EditMenu',
                            ),
                        ),
                    ),
                    'EditMenuItem' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/EditMenuItem[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Menu',
                                'method' => 'EditMenuItem',
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