<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Installer implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
    
    public function install()
    {
        $configManager = $this->serviceManager->get('configManager');
        $db = $this->serviceManager->get('db');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $configManager->set('users', 'allow_registration', false);
        
        $guid = 'user-item';
        if (null === ($id = $objectTypesCollection->getTypeByGuid($guid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::Users:User object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($guid)->save();
        }
        if (!$configManager->has('users', 'new_user_object_type')) {
            $configManager->set('users', 'new_user_object_type', $id);
        }        
        
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
}