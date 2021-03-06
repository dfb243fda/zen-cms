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
        'loginzaPageTemplate' => 'page_template/Users/loginza.phtml',
        
        // При авторизации можно будет вводить значения этих полей
        'authIdentityFields' => array('email', 'login'),
        
        'authAdapters' => array( 
            'simple' => array(
                100 => 'Users\Authentication\Adapter\Db',
            ),
            'loginza' => array(
                100 => 'Users\Authentication\Adapter\Loginza',
            ),
        ),
        
        // При регистрации будет поле с логином
        'enableLogin' => false,
        
        // При регистрации будет поле с отображаемым именем
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
            'Users\Service\Users' => 'Users\Service\Users',
            'Users\Service\UsersList' => 'Users\Service\UsersList',
            
            'Users\Service\Installer' => 'Users\Service\Installer',
            'Users\Service\Loginza' => 'Users\Service\Loginza',
            
            'Users\Collection\Users' => 'Users\Collection\Users',
            'Users\Entity\User' => 'Users\Entity\User',
            
            'Users\Service\UserRegistration' => 'Users\Service\UserRegistration',
            'Users\Service\UserAuthentication' => 'Users\Service\UserAuthentication',
            'Users\Service\UserData' => 'Users\Service\UserData',
            
            'Users\Authentication\Adapter\Db' => 'Users\Authentication\Adapter\Db',
            'Users\Authentication\Adapter\Loginza' => 'Users\Authentication\Adapter\Loginza',
            'Users\Authentication\Storage\Db' => 'Users\Authentication\Storage\Db',
            
            'Users\View\RendererStrategyOptions' => 'Users\View\RendererStrategyOptions',
            
            'Users\View\ResultComposer\HtmlComposer' => 'Users\View\ResultComposer\HtmlComposer',
            
            'Users\FormFactory\RegistrationFormFactory' => 'Users\FormFactory\RegistrationFormFactory',            
            'Users\FormFactory\UserFormFactory' => 'Users\FormFactory\UserFormFactory',
        ),
        'factories' => array(
            'Users\Authentication\Adapter\AdapterChain' => 'Users\Authentication\Adapter\AdapterChainServiceFactory',  
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
            'loginza' => array(
                'title' => 'i18n::Loginza config tab',
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
                                            'label' => 'Dynamic config allow registration',
                                            'description' => 'Dynamic config allow registration description',
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
                                            'label' => 'Users:Dynamic config send welcome email to reg users',
                                            'description' => 'Users:Dynamic config send welcome email to reg users description',
                                        ),
                                     ),
                                ),
                                array(
                                    'spec' => array(
                                        'type' => 'text',
                                        'name' => 'welcome_email_subject',
                                        'options' => array(
                                            'label' => 'Users:Dynamic config welcome email subject',
                                            'description' => 'Users:Dynamic config welcome email subject description',
                                        ),
                                    ),
                                ),
                                array(
                                    'spec' => array(
                                        'type' => 'ckEditor',
                                        'name' => 'welcome_email_text',
                                        'options' => array(
                                            'label' => 'Users:Dynamic config welcome email text',
                                            'description' => 'Users:Dynamic config welcome email text description',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'loginza' => array(
                'fieldsets' => array(
                    array(
                        'spec' => array(
                            'name' => 'loginza',
                            'elements' => array(
                                array(
                                    'spec' => array(
                                        'type' => 'Collection',
                                        'name' => 'domains',
                                        'options' => array(
                                            'count' => 1,
                                            'should_create_template' => true,
                                            'allow_add' => true,
                                            'target_element' => array(
                                                'type' => 'Users\Fieldset\LoginzaFieldset'
                                            )
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
            'loginza' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/loginza[/:action{/.}][/][.:format]',
                    'defaults' => array(
                        'controller' => 'Users\Controller\Loginza',
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
            'Users\Controller\Loginza' => 'Users\Controller\LoginzaController',
        ),
    ),
);