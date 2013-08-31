<?php

namespace Elfinder\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Connector implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
 
    public function run()
    {
        $response = $this->serviceManager->get('response');
        $paramsPlugin = $this->serviceManager->get('ControllerPluginManager')->get('params');
        
        $response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        $opts = array(
            'debug' => true,
            'roots' => array(

            ),            
        );
 
        if (null === $paramsPlugin->fromQuery('dirs')) {
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
            $dirs = (string)$paramsPlugin->fromQuery('dirs');
            $dirs = explode(',', $dirs);
            
            $dirsInPublic = $this->serviceManager->get('fileManager')->getDirs(PUBLIC_PATH);  
            
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
                        'tmbPath'       => PUBLIC_PATH . '/temp/elfinder_tmb/',
                        'tmbURL'        => ROOT_URL_SEGMENT . '/temp/elfinder_tmb/',
                        'quarantine'    => '../temp/elfinder_quarantine/',
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