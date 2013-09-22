<?php

return array(
    'Catalog' => array(
        'title' => 'i18n::Catalog module',
        'description' => 'i18n::Catalog module description',
        'version' => '0.1',
        
        'methods' => array(
            'ProductList' => array(
                'service' => 'Catalog\Method\ProductList',
                'title' => 'i18n::ProductList method',
                'description' => 'i18n::ProductList method description',
                'type' => 'be',
                'menu_group' => 'catalog',
            ),
            'EditCategory' => array(
                'service' => 'Catalog\Method\EditCategory',
                'title' => 'i18n::Catalog:EditCategory method',
                'description' => 'i18n::Catalog:EditCategory method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'EditProduct' => array(
                'service' => 'Catalog\Method\EditProduct',
                'title' => 'i18n::Catalog:EditProduct method',
                'description' => 'i18n::Catalog:EditProduct method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'AddCategory' => array(
                'service' => 'Catalog\Method\AddCategory',
                'title' => 'i18n::Catalog:AddCategory method',
                'description' => 'i18n::Catalog:AddCategory method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'AddProduct' => array(
                'service' => 'Catalog\Method\AddProduct',
                'title' => 'i18n::Catalog:AddProduct method',
                'description' => 'i18n::Catalog:AddProduct method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'ProductList',
            ),
            'DeleteCategory' => array(
                'service' => 'Catalog\Method\DeleteCategory',
                'title' => 'i18n::Catalog:DeleteCategory method',
                'description' => 'i18n::Catalog:DeleteCategory method description',
                'type' => 'be',
            ),
            'DeleteProduct' => array(
                'service' => 'Catalog\Method\DeleteProduct',
                'title' => 'i18n::Catalog:DeleteProduct method',
                'description' => 'i18n::Catalog:DeleteProduct method description',
                'type' => 'be',
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'Catalog\Service\Installer' => 'Catalog\Service\Installer',
            'Catalog\Service\ProductsTree' => 'Catalog\Service\ProductsTree',
            'Catalog\Service\Catalog' => 'Catalog\Service\Catalog',
            'Catalog\Collection\CategoriesCollection' => 'Catalog\Collection\CategoriesCollection',
            'Catalog\Collection\ProductsCollection' => 'Catalog\Collection\ProductsCollection',
            
            'Catalog\FormFactory\CategoryFormFactory' => 'Catalog\FormFactory\CategoryFormFactory',
            'Catalog\FormFactory\ProductFormFactory' => 'Catalog\FormFactory\ProductFormFactory',
            
            'Catalog\Entity\CategoryEntity' => 'Catalog\Entity\CategoryEntity',
            'Catalog\Entity\ProductEntity' => 'Catalog\Entity\ProductEntity',
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
                    'EditCategory' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/EditCategory[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'EditCategory',
                            ),
                        ),
                    ),
                    'EditProduct' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/EditProduct[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'EditProduct',
                            ),
                        ),
                    ),
                    'AddCategory' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Menu/AddCategory[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'AddCategory',
                            ),
                        ),
                    ),                   
                    'AddProduct' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Catalog/AddProduct[/id_:id{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Catalog',
                                'method' => 'AddMenuItem',
                            ),
                        ),
                    )
                ),
            ),
        ),
    ),    
);