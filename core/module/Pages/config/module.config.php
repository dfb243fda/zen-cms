<?php

return array(
    'Pages' => array(
        'title' => 'i18n::Pages module',
        'description' => 'i18n::Pages module description',
        'version' => '0.1',
        
        'priority' => -5,
        'isRequired' => true,
        
        'methods' => array(    
            'PagesList' => array(
                'service' => 'Pages\Method\PagesList',
                'title' => 'i18n::Pages list method',
                'description' => 'i18n::Pages list method description',
                'type' => 'be',
                'menu_group' => 'pages',
            ),
            'AddPage' => array(
                'service' => 'Pages\Method\AddPage',
                'title' => 'i18n::Add page method',
                'description' => 'i18n::Add page method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'PagesList',
            ),
            'EditPage' => array(
                'service' => 'Pages\Method\EditPage',
                'title' => 'i18n::Edit page method',
                'description' => 'i18n::Edit page method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'PagesList',
            ),
            'DeletePage' => array(
                'service' => 'Pages\Method\DeletePage',
                'title' => 'i18n::Delete page method',
                'description' => 'i18n::Delete page method description',
                'directAccess' => true,
            ),
            'PageContent' => array(
                'service' => 'Pages\Method\PageContent',
                'title' => 'i18n::Page content method',
                'description' => 'i18n::Page content method description',
                'directAccess' => true,
            ),            
            'AddContent' => array(
                'service' => 'Pages\Method\AddContent',
                'title' => 'i18n::Add content method',
                'description' => 'i18n::Add content method description',
                'type' => 'be',
            ),
            'EditContent' => array(
                'service' => 'Pages\Method\EditContent',
                'title' => 'i18n::Edit content method',
                'description' => 'i18n::Edit content method description',
                'type' => 'be',
            ),
            'DeactivateContent' => array(
                'service' => 'Pages\Method\DeactivateContent',
                'title' => 'i18n::Deactivate content method',
                'description' => 'i18n::Deactivate content method description',
                'directAccess' => true,
            ),
            'ActivateContent' => array(
                'service' => 'Pages\Method\ActivateContent',
                'title' => 'i18n::Activate content method',
                'description' => 'i18n::Activate content method description',
                'directAccess' => true,
            ),
            'DeleteContent' => array(
                'service' => 'Pages\Method\DeleteContent',
                'title' => 'i18n::Delete content method',
                'description' => 'i18n::Delete content method description',
                'directAccess' => true,
            ),
            'SortContent' => array(
                'service' => 'Pages\Method\SortContent',
                'title' => 'i18n::Sort content method',
                'description' => 'i18n::Sort content method description',
                'directAccess' => true,
            ),
            'DomainsList' => array(
                'service' => 'Pages\Method\DomainsList',
                'title' => 'i18n::Domains list method',
                'description' => 'i18n::Domains list method description',
                'type' => 'be',
                'menu_group' => 'pages',
            ),
            'EditDomain' => array(
                'service' => 'Pages\Method\EditDomain',
                'title' => 'i18n::Edit domain method',
                'description' => 'i18n::Edit domain method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'DomainsList',
            ),
            'AddDomain' => array(
                'service' => 'Pages\Method\AddDomain',
                'title' => 'i18n::Add domain method',
                'description' => 'i18n::Add domain method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'DomainsList',
            ),
            'DeleteDomain' => array(
                'service' => 'Pages\Method\DeleteDomain',
                'title' => 'i18n::Delete domain method',
                'description' => 'i18n::Delete domain method description',
                'type' => 'be',
            ),
        ),
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'Pages\Service\PagesTree' => 'Pages\Service\PagesTree',
            
            'Pages\Service\Page' => 'Pages\Service\Page',
        ),
    ),
    
    'dynamic_config' => array(
        'tabs' => array(
            'pages' => array(
                'title' => 'i18n::Pages config tab',
            ),
        ),
        'form' => array(
            'pages' => array(
                'fieldsets' => array(
                    'pages' => array(
                        'spec' => array(
                            'name' => 'pages',
                            'elements' => array(
                                'replace_spaces_with' => array(
                                    'spec' => array(
                                        'name' => 'replace_spaces_with',
                                        'options' => array(
                                            'label' => 'i18n::Pages:change spaces with',
                                            'description' => 'i18n::Pages:change spaces with description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'input_filter' => array(
                    'pages' => array(
                        'type' => 'Zend\InputFilter\InputFilter',
                        'replace_spaces_with' => array(
                            'required' => true,
                        ),
                    ),
                ),
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
        'pages' => array(
            'title' => 'i18n::Pages menu group',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'PagesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Pages/PagesList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Pages',
                                'method' => 'PagesList',
                            ),
                        ),
                    ),
                    'AddPage' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Pages/AddPage[/domain_:domainId{/.}][/page_type_:pageTypeId{/.}][/page_:pageId{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Pages',
                                'method' => 'AddPage',
                            ),
                        ),
                    ),
                    'EditPage' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Pages/EditPage/:id{/.}[/page_type_:pageTypeId{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Pages',
                                'method' => 'EditPage',
                            ),
                        ),
                    ),
                    'AddContent' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Pages/AddContent/:markerId/:beforeContentId/:pageId{/.}[/page_content_type_:pageContentTypeId{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Pages',
                                'method' => 'AddContent',
                            ),
                        ),
                    ),
                    'EditContent' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Pages/EditContent/:id{/.}[/page_content_type_:pageContentTypeId{/.}][/object_type_:objectTypeId{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Pages',
                                'method' => 'EditContent',
                            ),
                        ),
                    ),
                    'DomainsList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Pages/DomainsList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Pages',
                                'method' => 'DomainsList',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    
);