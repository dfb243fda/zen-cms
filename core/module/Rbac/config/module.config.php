<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'Rbac' => array(
        'title' => 'i18n::Rbac module',
        'description' => 'i18n::Rbac module description',
        'version' => '0.1',
        'isRequired' => true,
        
        'methods' => array(
            'Permissions' => array(
                'service' => 'Rbac\Method\Permissions',
                'title' => 'i18n::Permissions method',
                'description' => 'i18n::Permissions method description',
                'type' => 'be',
                'menu_group' => 'users',
            ),
            
            'RolesList' => array(
                'service' => 'Rbac\Method\RolesList',
                'title' => 'i18n::Rbac:Roles list method',
                'description' => 'i18n::Rbac:Roles list method description',
                'type' => 'be',
                'menu_group' => 'users',
            ),
            'EditRole' => array(
                'service' => 'Rbac\Method\EditRole',
                'title' => 'i18n::Rbac:Roles edit method',
                'description' => 'i18n::Rbac:Roles edit method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'RolesList',
            ),
            'AddRole' => array(
                'service' => 'Rbac\Method\AddRole',
                'title' => 'i18n::Rbac:Add roles method',
                'description' => 'i18n::Rbac:Add roles method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'RolesList',
            ),
            'DeleteRole' => array(
                'service' => 'Rbac\Method\DeleteRole',
                'title' => 'i18n::Rbac:Roles delete method',
                'description' => 'i18n::Rbac:Roles delete method description',
                'type' => 'be',
            ),
        ),
        
        // identity provider service name
        'identity_provider'     => 'Rbac\Provider\Identity\UsersZendDb',

        // Role providers to be used to load all available roles into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'role_providers'        => array(
            'Rbac\Provider\Role\ZendDb' => array(
                'table'             => 'roles',
                'role_id_field'     => 'id',
                'parent_role_field' => 'parent',
            ),
        ),

        // Resource providers to be used to load all available resources into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'resource_providers'    => array(
//            'Rbac\Provider\Resource\Config' => array(
//                'admin_access' => array(),
//            ),
            'Rbac\Provider\Resource\ZendDb' => array(),
        ),

        // Rule providers to be used to load all available rules into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'rule_providers'        => array(
//            'Rbac\Provider\Rule\Config' => array(
//                'allow' => array(
                    // allow guests and users (and admins, through inheritance)
                    // the "wear" privilege on the resource "pants"
//                    array(array('id_0'), 'admin_access')
//                ),
//            ),
            'Rbac\Provider\Rule\ZendDb' => array(),
        ),

        // Guard listeners to be attached to the application event manager
        'guards'                => array(),

        // strategy service name for the strategy listener to be used when permission-related errors are detected
        'unauthorized_strategy' => 'Rbac\View\UnauthorizedStrategy',

        // Template name for the unauthorized strategy
        'template'              => 'page_template/Rbac/403.phtml',
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
            'Rbac\Service\NewUserRolesSetter' => 'Rbac\Service\NewUserRolesSetter',
            'Rbac\Service\ModulePermissionsCollector' => 'Rbac\Service\ModulePermissionsCollector',            
        ),
        'factories' => array(
            'Rbac\Service\Authorize'                   => 'Rbac\Service\AuthorizeFactory',
            'Rbac\View\UnauthorizedStrategy'           => 'Rbac\Service\UnauthorizedStrategyServiceFactory',
            'Rbac\Guards'                              => 'Rbac\Service\GuardsServiceFactory',            
            'Rbac\RoleProviders'                       => 'Rbac\Service\RoleProvidersServiceFactory',
            'Rbac\ResourceProviders'                   => 'Rbac\Service\ResourceProvidersServiceFactory',
            'Rbac\RuleProviders'                       => 'Rbac\Service\RuleProvidersServiceFactory',
            'Rbac\Provider\Identity\UsersZendDb'       => 'Rbac\Service\UsersZendDbIdentityProviderServiceFactory',
        ),
    ),
    
//    'view_manager' => array(
//        'template_map' => array(
//            'error/403' => APPLICATION_PATH . '/view/page_template/Rbac/403.phtml',
//        ),
//    ),
    
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'RolesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Rbac/RolesList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Rbac',
                                'method' => 'RolesList',
                            ),
                        ),
                    ),
                ),
            ),  
        ),
    ),
    
/*    
    '--service_manager--' => array(
        'factories' => array(
            'BjyAuthorize\Config'                   => 'BjyAuthorize\Service\ConfigServiceFactory',
            'BjyAuthorize\Guards'                   => 'BjyAuthorize\Service\GuardsServiceFactory',
            'BjyAuthorize\RoleProviders'            => 'BjyAuthorize\Service\RoleProvidersServiceFactory',
            'BjyAuthorize\ResourceProviders'        => 'BjyAuthorize\Service\ResourceProvidersServiceFactory',
            'BjyAuthorize\RuleProviders'            => 'BjyAuthorize\Service\RuleProvidersServiceFactory',
            'BjyAuthorize\Guard\Controller'         => 'BjyAuthorize\Service\ControllerGuardServiceFactory',
            'BjyAuthorize\Guard\Route'              => 'BjyAuthorize\Service\RouteGuardServiceFactory',
            'BjyAuthorize\Provider\Role\Config'     => 'BjyAuthorize\Service\ConfigRoleProviderServiceFactory',
            'BjyAuthorize\Provider\Role\ZendDb'     => 'BjyAuthorize\Service\ZendDbRoleProviderServiceFactory',
            'BjyAuthorize\Provider\Resource\Config' => 'BjyAuthorize\Service\ConfigResourceProviderServiceFactory',
            'BjyAuthorize\Service\Authorize'        => 'BjyAuthorize\Service\AuthorizeFactory',
            'BjyAuthorize\Provider\Identity\ProviderInterface'                => 'BjyAuthorize\Service\IdentityProviderServiceFactory',
            'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider'                => 'BjyAuthorize\Service\AuthenticationIdentityProviderServiceFactory',
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider'                => 'BjyAuthorize\Service\ObjectRepositoryRoleProviderFactory',
            'BjyAuthorize\Collector\RoleCollector'  => 'BjyAuthorize\Service\RoleCollectorServiceFactory',
            'BjyAuthorize\Provider\Identity\ZfcUserZendDb' => 'BjyAuthorize\Service\ZfcUserZendDbIdentityProviderServiceFactory',
            'BjyAuthorize\View\UnauthorizedStrategy'  => 'BjyAuthorize\Service\UnauthorizedStrategyServiceFactory',
        ),
        'invokables'  => array(
            'BjyAuthorize\View\RedirectionStrategy' => 'BjyAuthorize\View\RedirectionStrategy',
        ),
        'aliases'     => array(
            'bjyauthorize_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ),
        'initializers' => array(
            'BjyAuthorize\Service\AuthorizeAwareServiceInitializer'  => 'BjyAuthorize\Service\AuthorizeAwareServiceInitializer'
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
            'error/403' => __DIR__ . '/../view/error/403.phtml',
            'zend-developer-tools/toolbar/bjy-authorize-role'
                => __DIR__ . '/../view/zend-developer-tools/toolbar/bjy-authorize-role.phtml',
        ),
    ),

    'zenddevelopertools' => array(
        'profiler' => array(
            'collectors' => array(
                'bjy_authorize_role_collector' => 'BjyAuthorize\\Collector\\RoleCollector',
            ),
        ),
        'toolbar' => array(
            'entries' => array(
                'bjy_authorize_role_collector' => 'zend-developer-tools/toolbar/bjy-authorize-role',
            ),
        ),
    ),

*/
    
);
