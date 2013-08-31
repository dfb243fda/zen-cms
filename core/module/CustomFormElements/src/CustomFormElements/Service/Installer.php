<?php

namespace CustomFormElements\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FormsList implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
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
                'title' => 'i18n::Page link field type',
                'is_multiple' => 0,
                'name' => 'pageLink',
            ),
            array(
                'title' => 'i18n::Ckeditor field type',
                'is_multiple' => 0,
                'name' => 'ckEditor',
            ),
            array(
                'title' => 'i18n::Time field type',
                'is_multiple' => 0,
                'name' => 'timePicker',
            ),
            array(
                'title' => 'i18n::Date time field type',
                'is_multiple' => 0,
                'name' => 'dateTimePicker',
            ),
            array(
                'title' => 'i18n::Date picker field type',
                'is_multiple' => 0,
                'name' => 'datePicker',
            ),            
            array(
                'title' => 'i18n::Image field type',
                'is_multiple' => 0,
                'name' => 'image',
            ),  
            array(
                'title' => 'i18n::Multi text field type',
                'is_multiple' => 1,
                'name' => 'multiText',
            ),  
            array(
                'title' => 'i18n::Composite field type',
                'is_multiple' => 1,
                'name' => 'composite',
            ),  
            array(
                'title' => 'i18n::Color picker field type',
                'is_multiple' => 0,
                'name' => 'colorPicker',
            ),  
        );
        
        foreach ($fields as $v) {
            $sqlRes = $db->query('select count(id) as cnt from ' . DB_PREF . 'object_field_types where name = ?', array($v['name']))->toArray();
            
            if (0 == $sqlRes[0]['cnt']) {
                $db->query('
                    insert into ' . DB_PREF . 'object_field_types
                        (title, is_multiple, name)
                    values (?, ?, ?)', array($v['title'], $v['is_multiple'], $v['name']));
            }            
        }
    }
}