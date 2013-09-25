<?php

namespace Logger\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Log\Logger as ZendLogger;

class Logger implements ServiceManagerAwareInterface
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

    public function init()
    {
        $locator = $this->serviceManager;
        $config = $locator->get('config');
        $logger = $locator->get('logger');
        $request = $locator->get('request');
        
        $logger->addProcessor('App\Log\Processor\FullBacktrace');
        $logger->addProcessor('Zend\Log\Processor\Backtrace');
        $logger->addProcessor('App\Log\Processor\Request', 1, array(
            'request' => $request,
        ));        
        
        foreach ($config['Logger']['log_writers'] as $writer => $value) {
            switch ($writer) {
                case 'db':
                    $logDbWriterOptions = $value['options'];
                    $logDbWriterOptions['db'] = $locator->get('db');
                    $logDbWriterOptions['table'] = DB_PREF . $logDbWriterOptions['table'];
                    
                    $filter = new \Zend\Log\Filter\Priority($value['priority']);
                    
                    $logDbWriter = new \App\Log\Writer\Db($logDbWriterOptions);
                    $logDbWriter->addFilter($filter);
                    
                    $logger->addWriter($logDbWriter);
                    
                    break;
                
                case 'bugHunter':
                    $filter = new \Zend\Log\Filter\Priority($value['priority']);
                    $logBugHunterWriter = $locator->get('bugHunter');
                    $logBugHunterWriter->addFilter($filter);
                    
                    $logger->addWriter($logBugHunterWriter);
                    
                    break;
            }
        }
        
        register_shutdown_function(array($this, 'fatalErrorShutdownHandler'), $logger);
    }
    
    public function fatalErrorShutdownHandler($logger)
    {
        $lastError = error_get_last();
        if ($lastError['type'] === E_ERROR) {
            $logger->log(ZendLogger::ERR, $lastError['message'], array(
                'file' => $lastError['file'],
                'line' => $lastError['line'],
            ));
        }
    }
}