<?php

namespace ObjectTypes\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Installer implements ServiceManagerAwareInterface
{
    /**
     * @var type Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
 
    public function install()
    {
        $db = $this->serviceManager->get('db');
        
        $fields = array(
            array(
                'title' => 'i18n::Checkbox field type',
                'is_multiple' => 0,
                'name' => 'checkbox',
            ),
            array(
                'title' => 'i18n::Password field type',
                'is_multiple' => 0,
                'name' => 'password',
            ),
            array(
                'title' => 'i18n::Select list field type',
                'is_multiple' => 0,
                'name' => 'select',
            ),
            array(
                'title' => 'i18n::Multiselect field type',
                'is_multiple' => 1,
                'name' => 'select',
            ),
            array(
                'title' => 'i18n::Textarea field type',
                'is_multiple' => 0,
                'name' => 'textarea',
            ),
            array(
                'title' => 'i18n::Text field type',
                'is_multiple' => 0,
                'name' => 'text',
            ),
            array(
                'title' => 'i18n::Multi checkbox field type',
                'is_multiple' => 1,
                'name' => 'multiCheckbox',
            ),            
            array(
                'title' => 'i18n::URL field type',
                'is_multiple' => 0,
                'name' => 'url',
            ),            
            array(
                'title' => 'i18n::Number field type',
                'is_multiple' => 0,
                'name' => 'number',
            ),            
        );
        
        foreach ($fields as $v) {
            $sqlRes = $db->query('select count(id) as cnt from ' . DB_PREF . 'object_field_types where name = ? and is_multiple = ?', array($v['name'], $v['is_multiple']))->toArray();
            
            if (0 == $sqlRes[0]['cnt']) {
                $db->query('
                    insert into ' . DB_PREF . 'object_field_types
                        (title, is_multiple, name)
                    values (?, ?, ?)', array($v['title'], $v['is_multiple'], $v['name']));
            }            
        }
    }
    
}