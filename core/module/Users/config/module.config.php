<?php

return array(
    'Users' => array(
        'title' => 'i18n::Users module',        
        'description' => 'i18n::Users module description',
        'version' => '0.1',
        'isRequired' => true,
        
        'loginPageTemplate' => 'page_template/Users/login.phtml',
        'registerPageTemplate' => 'page_template/Users/register.phtml',
        'authIdentityFields' => array('email', 'username'),
        'authAdapters' => array( 
            100 => 'Users\Authentication\Adapter\Db',
        ),
        
        'enableUserName' => false,
        'enableDisplayName' => true,
        
        'registrationRedirectRoute' => 'admin',
        'loginAfterRegistration' => true,
        
        'useRedirectParameterIfPresent' => true,
        
        'loginRedirectRoute' => 'admin',
        'logoutRedirectRoute' => 'login',
        'enableUserState' => false,
        'passwordCost' => 14,
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'Users\Collection\Users' => 'Users\Collection\Users',
            'Users\Entity\User' => 'Users\Entity\User',
            
            'Users\Service\UserRegistration' => 'Users\Service\UserRegistration',
            'Users\Service\UserAuthentication' => 'Users\Service\UserAuthentication',
            'Users\Service\UserData' => 'Users\Service\UserData',
            
            'Users\Authentication\Adapter\Db' => 'Users\Authentication\Adapter\Db',
            'Users\Authentication\Storage\Db' => 'Users\Authentication\Storage\Db',
            
            'Users\View\LoginRendererStrategyOptions' => 'Users\View\LoginRendererStrategyOptions',
            'Users\View\RegistrationRendererStrategyOptions' => 'Users\View\RegistrationRendererStrategyOptions',
            
            'Users\View\ResultComposer\LoginHtmlComposer' => 'Users\View\ResultComposer\LoginHtmlComposer',
            'Users\View\ResultComposer\RegistrationHtmlComposer' => 'Users\View\ResultComposer\RegistrationHtmlComposer',
        ),
        'factories' => array(
            'Users\Authentication\Adapter\AdapterChain' => 'Users\Authentication\Adapter\AdapterChainServiceFactory',  
        ),
    ),
    
    'view_helpers' => array(
        'invokables' => array(
            'UserDisplayName' => 'Users\View\Helper\UserDisplayName',
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
        'users' => array(
            'title' => 'i18n::Users menu group',
        ),
    ),
    
    'dynamic_config' => array(
        'tabs' => array(
            'registration' => array(
                'title' => 'i18n::Registration config tab',
            ),
        ),
        'form' => array(
            'registration' => array(
                'fieldsets' => array(
                    array(
                        'spec' => array(
                            'name' => 'users',
                            'elements' => array(
                                array(
                                    'spec' => array(
                                        'type' => 'checkbox',
                                        'name' => 'allow_registration',
                                        'options' => array(
                                            'label' => 'i18n::Dynamic config allow registration',
                                            'description' => 'i18n::Dynamic config allow registration description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'spec' => array(
                            'name' => 'registration',
                            'elements' => array(
                                array(
                                    'spec' => array(
                                        'type' => 'checkbox',
                                        'name' => 'send_welcome_email_to_reg_users',
                                        'options' => array(
                                            'label' => 'i18n::Users:Dynamic config send welcome email to reg users',
                                            'description' => 'i18n::Users:Dynamic config send welcome email to reg users description',
                                        ),
                                     ),
                                ),
                                array(
                                    'spec' => array(
                                        'type' => 'text',
                                        'name' => 'welcome_email_subject',
                                        'options' => array(
                                            'label' => 'i18n::Users:Dynamic config welcome email subject',
                                            'description' => 'i18n::Users:Dynamic config welcome email subject description',
                                        ),
                                    ),
                                ),
                                array(
                                    'spec' => array(
                                        'type' => 'ckEditor',
                                        'name' => 'welcome_email_text',
                                        'options' => array(
                                            'label' => 'i18n::Users:Dynamic config welcome email text',
                                            'description' => 'i18n::Users:Dynamic config welcome email text description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'UsersList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'Users/UsersList[/:task{/.}][/][.:format]',
                            'defaults' => array(
                                'module' => 'Users',
                                'method' => 'UsersList',
                            ),
                        ),
                    ),
                ),
            ),    
            'login' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/login[/][.:format]',
                    'defaults' => array(
                        'controller' => 'Users\Controller\Login',
                        'action'     => 'index',
                    ),
                ),
            ),
            'register' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/register[/][.:format]',
                    'defaults' => array(
                        'controller' => 'Users\Controller\Register',
                        'action'     => 'index',
                    ),
                ),
            ),
            'logout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/logout[/][.:format]',
                    'defaults' => array(
                        'controller' => 'Users\Controller\Logout',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    
    'controllers' => array(
        'invokables' => array(
            'Users\Controller\Login' => 'Users\Controller\LoginController',
            'Users\Controller\Logout' => 'Users\Controller\LogoutController',
            'Users\Controller\Register' => 'Users\Controller\RegisterController',
        ),
    ),
);