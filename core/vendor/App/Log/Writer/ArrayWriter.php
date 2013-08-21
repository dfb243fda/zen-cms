<?php

namespace App\Log\Writer;

use Zend\Log\Writer\AbstractWriter;

class ArrayWriter extends AbstractWriter
{
    protected $events = array();
    
    protected function doWrite(array $event)
    {
        $event['timestamp'] = $event['timestamp']->format('U');
        $this->events[] = $event;
    }
    
    public function getLogs()
    {
        return $this->events;
    }
    
    public function hasLogs()
    {
        return (count($this->events) > 0);
    }
}