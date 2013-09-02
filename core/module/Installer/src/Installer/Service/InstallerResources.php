<?php

namespace Installer\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class InstallerResources implements ServiceManagerAwareInterface
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
    
    public function copyInstallerResources()
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        $config = $this->serviceManager->get('applicationConfig');
        
        $dirs = $fileManager->getDirs($config['path']['core_public_resources']);
        foreach ($dirs as $dir) {
            if (!is_dir($config['path']['public'] . '/' . $dir . '/core')) {
                $fileManager->mkdir($config['path']['public'] . '/' . $dir . '/core', true);
            }
            $fileManager->recurseCopy($config['path']['core_public_resources'] . '/' . $dir, $config['path']['public'] . '/' . $dir . '/core');
        }
        
        $dirs = $fileManager->getDirs($config['path']['core_view']);
        foreach ($dirs as $dir) {
            if (!is_dir($config['path']['application_view'] . '/' . $dir . '/core')) {
                $fileManager->mkdir($config['path']['application_view'] . '/' . $dir . '/core', true);
            }
            $fileManager->recurseCopy($config['path']['core_view'] . '/' . $dir, $config['path']['application_view'] . '/' . $dir . '/core');
        }        
          
        if (!is_dir($config['path']['application_temp'])) {
            $fileManager->mkdir($config['path']['application_temp'], true);
        }
        if (!is_dir($config['path']['application_files'])) {
            $fileManager->mkdir($config['path']['application_files'], true);
        }
        if (!is_dir($config['path']['public_uploads'])) {
            $fileManager->mkdir($config['path']['public_uploads'], true);
        }
        if (!is_dir($config['path']['public_temp'])) {
            $fileManager->mkdir($config['path']['public_temp'], true);
        }
        if (!is_dir($config['path']['public_files'])) {
            $fileManager->mkdir($config['path']['public_files'], true);
        }
        
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $moduleManager->copyModuleViewToApplication('Installer');
        
        $moduleManager->shareModulePublicResources('Installer');
    }
    
}