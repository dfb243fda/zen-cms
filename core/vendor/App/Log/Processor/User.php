<?php

namespace App\Log\Processor;

use Zend\Log\Processor\ProcessorInterface;

class User implements ProcessorInterface
{
    protected $userData;
    
    public function __construct($options)
    {             
        $this->userData = $options['userData'];
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
        if (null === $this->userData) {
            $event['extra']['user_id'] = 0;
        } else {
            $event['extra']['user_id'] = $this->userData->getId();
        }
        return $event;
    }
}