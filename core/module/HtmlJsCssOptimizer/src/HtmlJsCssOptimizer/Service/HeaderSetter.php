<?php

namespace HtmlJsCssOptimizer\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Munee\Asset\HeaderSetter as MuneeHeaderSetter;

class HeaderSetter extends MuneeHeaderSetter implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    /**
     * Set HTTP status code.
     *
     * @param string $protocol
     * @param string $code
     * @param string $message
     * 
     * @return object
     */
    public function statusCode($protocol, $code, $message)
    {
        $response = $this->serviceLocator->get('response');
        
        $response->setStatusCode($code);
        
//        header("{$protocol} {$code} {$message}");
        
        return $this;
    }
    
    /**
     * Set HTTP header field/value.
     *
     * @param string $field
     * @param string $value
     * 
     * @return object
     */
    public function headerField($field, $value)
    {
        $response = $this->serviceLocator->get('response');
        
        $response->getHeaders()->addHeaderLine($field, $value);
        
//        header("{$field}: {$value}");
        
        return $this;
    }
}