<?php

namespace App\Log\Processor;

use Zend\Log\Processor\ProcessorInterface;

class FullBacktrace implements ProcessorInterface
{
    /**
     * Maximum stack level of backtrace (PHP > 5.4.0)
     * @var int
     */
    protected $traceLimit = 10;
    
    /**
     * Classes within this namespace in the stack are ignored
     * @var string
     */
    protected $ignoredNamespace = 'Zend\\Log';
    
    /**
     * Adds the origin of the log() call to the event extras
     *
     * @param array $event event data
     * @return array event data
    */
    public function process(array $event)
    {
        $trace = $this->getBacktrace();

        array_shift($trace); // ignore $this->getBacktrace();
        array_shift($trace); // ignore $this->process()

        $event['extra']['trace'] = $trace;

        return $event;
    }

    /**
     * Provide backtrace as slim as posible
     *
     * @return  array:
     */
    protected function getBacktrace()
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->traceLimit);
        }

        if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
            return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        return debug_backtrace();
    }
}