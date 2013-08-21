<?php

namespace Elfinder\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConnectorController extends AbstractActionController
{    
    protected $routeLogin = 'users/login';
    
    protected $bugHunter;
    
    protected $applicationConfig;
    
    protected $translator;
    
    protected $configManager;
    
    protected $moduleManager;
    
    protected $config;
    
//    protected $publicResources;
    
    protected $viewManager;
            
    protected function init()
    {   
        $this->bugHunter = $this->serviceLocator->get('bugHunter');  
        $this->applicationConfig = $this->serviceLocator->get('ApplicationConfig');        
        $this->translator = $this->serviceLocator->get('translator'); 
        $this->configManager = $this->serviceLocator->get('configManager');
        $this->moduleManager = $this->serviceLocator->get('moduleManager');           
        $this->config = $this->serviceLocator->get('config');
        $this->viewManager = $this->serviceLocator->get('viewManager');   
    }
    
    public function indexAction()
    {
        $this->init();
        
        if (!$this->isAllowed('file_system_access')) {	
            $this->response->setContent(json_encode(array('error' => 'У вас нет доступа к файловой системе')));
            return $this->response;
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        $opts = array(
            'debug' => true,
            'roots' => array(

            ),            
        );
 
        if (null === $this->params()->fromQuery('dirs')) {
            $opts['roots'][] = array(
                'driver'        => 'LocalFileSystem',            // driver for accessing file system (REQUIRED)
                'path'          => PUBLIC_PATH . '/',
                'URL'           => ROOT_URL_SEGMENT . '/', // URL to files (REQUIRED)
                'accessControl' => array($this, 'checkAccess'),       // disable and hide dot starting files (OPTIONAL)
                'alias'         => 'Public',
                'tmbPath' => PUBLIC_PATH . '/temp/elfinder_tmb/',
                'tmbURL' => ROOT_URL_SEGMENT . '/temp/elfinder_tmb/',
                'quarantine' => 'temp/elfinder_quarantine/',
            );
        } else {
            $dirs = (string)$this->params()->fromQuery('dirs');
            $dirs = explode(',', $dirs);
            
            $dirsInPublic = $this->serviceLocator->get('fileManager')->getDirs(PUBLIC_PATH);  
            
            $availableDirs = array();
            foreach ($dirsInPublic as $v) {
                if ('.' != substr($v, 0, 1)) {
                    $availableDirs[$v] = true;
                }
            }
            
            foreach ($dirs as $dir) {
                $dir = (string)$dir;
                if (isset($availableDirs[$dir])) {
                    $opts['roots'][] = array(
                        'driver'        => 'LocalFileSystem',            // driver for accessing file system (REQUIRED)
                        'path'          => PUBLIC_PATH . '/' . $dir . '/',
                        'URL'           => ROOT_URL_SEGMENT . '/' . $dir . '/', // URL to files (REQUIRED)
                        'accessControl' => array($this, 'checkAccess'),       // disable and hide dot starting files (OPTIONAL)
                        'alias'         => ucfirst($dir),
                        'tmbPath' => PUBLIC_PATH . '/temp/elfinder_tmb/',
                        'tmbURL' => ROOT_URL_SEGMENT . '/temp/elfinder_tmb/',
                        'quarantine' => '../temp/elfinder_quarantine/',
                    );
                }
            }
        }       
        
   
        $connector = new \elFinderConnector(new \elFinder($opts));        
     
        $connector->run();            
    }
    
    public function checkAccess($attr, $path, $data, $volume)
    {        
        return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            :  null;     
    }
    
}