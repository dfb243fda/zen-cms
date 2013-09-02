<?php

namespace Installer\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class CheckServer implements ServiceManagerAwareInterface
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
    
    public function getCheckServerResult()
    {        
        $translator = $this->serviceManager->get('translator');
        
        $result = array(
            'items' => array(),
            'success' => true,
        );
        
        $success = (version_compare(PHP_VERSION, '5.3.3') >= 0);
        $result['success'] = $result['success'] && $success;        
        $result['items'][] = array(
            'title' => $translator->translate('Installer php version >= 5.3.3'),
            'success' => $success,
        );
        
        
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $success = in_array('mod_rewrite', $modules);
        } else {
            $success =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;
        }
        $result['success'] = $result['success'] && $success;        
        $result['items'][] = array(
            'title' => $translator->translate('Installer mod_rewrite is on'),
            'success' => $success,
        );
        
        $success = false;
        if (extension_loaded('gd') && function_exists('gd_info')) {
            $success = true;
        }
        $result['success'] = $result['success'] && $success;        
        $result['items'][] = array(
            'title' => $translator->translate('Installer gd ext is installed'),
            'success' => $success,
        );
        
        $success = false;
        if (extension_loaded('intl') && class_exists('NumberFormatter', false)) {
            $success = true;
        }
        $result['success'] = $result['success'] && $success;
        $result['items'][] = array(
            'title' => $translator->translate('Installer intl ext is installed'),
            'success' => $success,
        );
        
        
        return $result;
    }
    
}