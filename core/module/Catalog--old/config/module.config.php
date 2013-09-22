<?php

return array(
    'Catalog' => array(
        'title' => 'i18n::Catalog module',
        'description' => 'i18n::Catalog module description',
        'version' => '0.1',
        
        'default_templates' => array(
            array(
                'title' => 'i18n::Catalog:Product list template',
                'name' => 'product_list.phtml',
                'type' => 'content_template',
                'method' => 'FeProductList',
            ),
            array(
                'title' => 'i18n::Catalog:Product item template',
                'name' => 'product_item.phtml',
                'type' => 'content_template',
                'method' => 'FeProductItem',
            ),
        ),
        
        'search_object_types' => array(
            array(
                'guid' => 'product',
                'with_descendants' => true,
            ),
        ),
        
        'methods' => array(
            'ProductList' => array(
                'service' => 'Catalog\Method\ProductList',
                'title' => 'i18n::Catalog:Product list method',
                'description' => 'i18n::Catalog:Product list method description',
                'type' => 'be',
                'menu_group' => 'catalog',
            ),
            'Edit' => array(
                'service' => 'Catalog\Method\Edit',
                'title' => 'i18n::Catalog:Catalog edit method',
                'description' => 'i18n::Catalog:Catalog edit method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'AddCatalog' => array(
                'service' => 'Catalog\Method\AddCatalog',
                'title' => 'i18n::Catalog:Add catalog method',
                'description' => 'i18n::Catalog:Add catalog method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'AddProduct' => array(
                'service' => 'Catalog\Method\AddProduct',
                'title' => 'i18n::Catalog:Add product method',
                'description' => 'i18n::Catalog:Add product method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'Delete' => array(
                'service' => 'Catalog\Method\Delete',
                'title' => 'i18n::Catalog:Catalog delete method',
                'description' => 'i18n::Catalog:Catalog delete method description',
                'type' => 'be',
            ),
            'FeProductList' => array(
                'service' => 'Catalog\Method\FeProductList',
                'title' => 'i18n::Catalog:FeProduct list method',
                'description' => 'i18n::Catalog:FeProduct list method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
            'FeProductItem' => array(
                'service' => 'Catalog\Method\FeProductItem',
                'title' => 'i18n::Catalog:FeProductItem method',
                'description' => 'i18n::Catalog:FeProductItem method description',
                'type' => 'fe_content',
                'dynamic_templates' => true,
            ),
        ),
    ),
    
    'search_url_query' => array(
        'product' => function($sm, $objectId) {
            $catalogService = $sm->get('Catalog\Service\Catalog');
            
            return $catalogService->getSingleProductUrlQuery($objectId);
        },
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
            'Catalog\Service\Catalog' => 'Catalog\Service\Catalog',
        ),
    ),
    
    'menu_groups' => array(
        'catalog' => array(
            'title' => 'i18n::Catalog menu group',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'ProductList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/ProductList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'ProductList',
                            ),
                        ),
                    ),
                    'AddCatalog' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/AddCatalog[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'AddCatalog',
                            ),
                        ),
                    ),
                    'EditCatalog' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/EditCatalog[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'Edit',
                            ),
                        ),
                    ),
                    'AddProduct' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/AddProduct[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'AddProduct',
                            ),
                        ),
                    )
                ),
            ),
        ),
    ),    
);