<?php

return array(
    'Users' => array(
        'title' => 'i18n::Users module',        
        'description' => 'i18n::Users module description',
        'version' => '0.1',
        'isRequired' => true,
        
        'default_templates' => array(
            array(
                'title' => 'i18n::Users:Login form template',
                'name' => 'login_form.phtml',
                'type' => 'content_template',
                'method' => 'LoginForm',
            ),
            array(
                'title' => 'i18n::Users:Registration form template',
                'name' => 'registration_form.phtml',
                'type' => 'content_template',
                'method' => 'RegistrationForm',
            ),
        ),
        
        'methods' => array(
            'UsersList' => array(
                'service' => 'Users\Method\UsersList',
                'title' => 'i18n::Users:Users list method',
                'description' => 'i18n::Users:Users list method description',
                'type' => 'be',
                'menu_group' => 'users',
            ),
            'EditUser' => array(
                'service' => 'Users\Method\EditUser',
                'title' => 'i18n::Users:Users edit method',
                'description' => 'i18n::Users:Users edit method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'UsersList',
            ),
            'AddUser' => array(
                'service' => 'Users\Method\AddUser',
                'title' => 'i18n::Users:Add user method',
                'description' => 'i18n::Users:Add user method description',
                'type' => 'be',
                'breadcrumbPrevMethod' => 'UsersList',
            ),
            'DeleteUser' => array(
                'service' => 'Users\Method\DeleteUser',
                'title' => 'i18n::Users:Users delete method',
                'description' => 'i18n::Users:Users delete method description',
                'type' => 'be',
            ),
            
            'LoginForm' => array(
                'service' => 'Users\Method\LoginForm',
                'title' => 'i18n::Users:Users LoginForm method',
                'description' => 'i18n::Users:Users LoginForm method description',
                'type' => 'fe_content',
            ),
            'RegistrationForm' => array(
                'service' => 'Users\Method\RegistrationForm',
                'title' => 'i18n::Users:Users RegistrationForm method',
                'description' => 'i18n::Users:Users RegistrationForm method description',
                'type' => 'fe_content',
            ),
        ),
        
        'loginPageTemplate' => 'page_template/Users/login.phtml',
        'registerPageTemplate' => 'page_template/Users/register.phtml',
        'authAdapters' => array( 
            100 => 'Users\Authentication\Adapter\Db',
        ),
        'authIdentityFields' => array('email', 'username'),
        'useRedirectParameterIfPresent' => true,
        'enableUserState' => false,
        'passwordCost' => 14,
        'userEntityClass' => 'Users\Entity\User',
        'tableName' => 'users',
        'loginRedirectRoute' => 'admin',
        'logoutRedirectRoute' => 'login',
        'enableUsername' => false,
        'enableDisplayName' => true,
        'useRegistrationFormCaptcha' => false,
        'loginAfterRegistration' => true,
    ),
    'menu_groups' => array(
        'users' => array(
            'title' => 'i18n::Users menu group',
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
    
    'service_manager' => array(
        'invokables' => array(
            'users_service' => 'Users\Service\User',
            'Users\Authentication\Storage\Db' => 'Users\Authentication\Storage\Db',
            'Users\Authentication\Adapter\Db' => 'Users\Authentication\Adapter\Db',   
            'users_register_form_hydrator'    => 'Zend\Stdlib\Hydrator\ClassMethods',
            'Users\Service\Installer' => 'Users\Service\Installer',
        ),
        'factories' => array(
            'Users\Authentication\Adapter\AdapterChain' => 'Users\Authentication\Adapter\AdapterChainServiceFactory',  
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