<?php

namespace CustomFormElements;

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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $db = $sm->get('db');
        
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
