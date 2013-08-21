<?php

namespace Installer\Demo;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Demo1 implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function createDemoSite()
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        $fileManager->recurseCopy(__DIR__ . '/Demo1/view', APPLICATION_PATH . '/view');
        $fileManager->recurseCopy(__DIR__ . '/Demo1/public', PUBLIC_PATH);
        
        $db = $this->serviceManager->get('db');
        
        $db->query('
            insert into ' . DB_PREF . 'templates
                (name, title, type, module, method, is_default)
            values (?, ?, ?, ?, ?, ?)
        ', array('default.phtml', 'Основной шаблон', 'page_template', 'FeTheme', '', 1));
        
    }
}