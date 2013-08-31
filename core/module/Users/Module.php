<?php

namespace Users;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function onBootstrap($e)
    {
        $app = $e->getTarget();
        
        $locator = $app->getServiceManager();
        $logger = $locator->get('logger');
        
        if ($locator->has('users_auth_service')) {
            $logger->addProcessor('App\Log\Processor\User', 1, array(
                'userData' => $locator->get('users_auth_service')->getIdentity(),
            ));
        }
    }

    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $configManager = $sm->get('configManager');
        
        $configManager->set('users', 'allow_registration', false);
        
        $objectTypesCollection = $sm->get('objectTypesCollection');
        
        $guid = 'user-item';
        if (null === ($id = $objectTypesCollection->getTypeByGuid($guid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::Users:User object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($guid)->save();
        }
        if (!$configManager->has('users', 'new_user_object_type')) {
            $configManager->set('users', 'new_user_object_type', $id);
        }
        
        $db = $sm->get('db');
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('Users', 'LoginForm'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $guid = 'login-form-content';
            if (null === ($id = $objectTypesCollection->getTypeByGuid($guid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Users:Login form object type');
                $objectType = $objectTypesCollection->getType($id);
                $objectType->setPageContentTypeId($contentTypeId)->setGuid($guid)->save();
            }
        }        
        
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('Users', 'RegistrationForm'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $guid = 'registration-form-content';
            if (null === ($id = $objectTypesCollection->getTypeByGuid($guid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Users:Registration form object type');
                $objectType = $objectTypesCollection->getType($id);
                $objectType->setPageContentTypeId($contentTypeId)->setGuid($guid)->save();
            }
        }
        
        
    }
    
    public function getDynamicConfig($sm)
    {
        $objectTypesCollection = $sm->get('objectTypesCollection');
        
        $guid = 'user-item';
        $parentId = $objectTypesCollection->getTypeIdByGuid($guid);
        
        $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($parentId);
        
        $typeIds = array_merge(array($parentId), $descendantTypeIds);
        
        $typeOptions = array();
        foreach ($typeIds as $typeId) {
            $objectType = $objectTypesCollection->getType($typeId);
            $typeOptions[$typeId] = $objectType->getName();
        }
        
        
        return array(
            'form' => array(
                'registration' => array(
                    'fieldsets' => array(
                        'users' => array(
                            'spec' => array(
                                'name' => 'users',
                                'elements' => array(
                                    'new_user_object_type' => array(
                                        'spec' => array(
                                            'type' => 'select',
                                            'name' => 'new_user_object_type',
                                            'options' => array(
                                                'label' => 'i18n::Users:Dynamic config new object type',
                                                'description' => 'i18n::Users:Dynamic config new object type description',
                                                'value_options' => $typeOptions,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'input_filter' => array(
                        'registration' => array(
                            'type' => 'Zend\InputFilter\InputFilter',
                            'new_user_object_type' => array(
                                'required' => true,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
    
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'UserAuthentication' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $authService = $serviceLocator->get('users_auth_service');
                    $authAdapter = $serviceLocator->get('Users\Authentication\Adapter\AdapterChain');
                    $controllerPlugin = new Controller\Plugin\UserAuthentication();
                    $controllerPlugin->setAuthService($authService);
                    $controllerPlugin->setAuthAdapter($authAdapter);
                    return $controllerPlugin;
                },
            ),
        );
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'userDisplayName' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserDisplayName;
                    $viewHelper->setAuthService($locator->get('users_auth_service'));
                    return $viewHelper;
                },
                'userIdentity' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserIdentity;
                    $viewHelper->setAuthService($locator->get('users_auth_service'));
                    return $viewHelper;
                },    
            ),        
            
/*            'factories' => array(
                'userDisplayName' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserDisplayName;
                    $viewHelper->setAuthService($locator->get('users_auth_service'));
                    return $viewHelper;
                },
                'userIdentity' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserIdentity;
                    $viewHelper->setAuthService($locator->get('users_auth_service'));
                    return $viewHelper;
                },
                'userLoginWidget' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserLoginWidget;
                    $viewHelper->setViewTemplate($locator->get('users_module_options')->getUserLoginWidgetViewTemplate());
                    $viewHelper->setLoginForm($locator->get('users_login_form'));
                    return $viewHelper;
                },
            ),
*/
        );

    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
}