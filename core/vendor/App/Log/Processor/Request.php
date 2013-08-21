<?php

namespace App\Log\Processor;

use Zend\Log\Processor\ProcessorInterface;

class Request implements ProcessorInterface
{
    protected $request;
    
    public function __construct($options)
    {             
        $this->request = $options['request'];
    }
    
    /**
     * Adds a identifier for the request to the log.
     *
     * This enables to filter the log for messages belonging to a specific request
     *
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        $event['extra']['uri'] = $this->request->getUriString();
        
        $remoteAddr = new \Zend\Http\PhpEnvironment\RemoteAddress();
        
        $event['extra']['client_ip'] = $remoteAddr->getIpAddress(); 
        return $event;
    }
}