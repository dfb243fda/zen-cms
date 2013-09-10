<?php

namespace Users\Service;

class AuthenticationAdapters
{    
    protected $adaptersType = 'simple';
    
    protected $allAuthAdapters;
    
    public function __construct($allAuthAdapters)
    {
        $this->allAuthAdapters = $allAuthAdapters;
    }
    
    public function setAdaptersType($type)
    {
        $this->adaptersType = $type;
        return $this;
    }
    
    public function getAdapters()
    {
        if (!isset($this->allAuthAdapters[$this->adaptersType])) {
            throw new \Exception('unknown adapter type ' . $this->adaptersType);
        }
        return $this->allAuthAdapters[$this->adaptersType];
    }    
}