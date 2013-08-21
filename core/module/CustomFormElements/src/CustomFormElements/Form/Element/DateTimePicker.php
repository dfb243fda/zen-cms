<?php

namespace CustomFormElements\Form\Element;

use Zend\Form\Element\DateTime;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class DateTimePicker extends DateTime implements ServiceLocatorAwareInterface
{
    protected $format = 'd-m-Y H:i';
    
    protected $serviceLocator;
    
    protected $jsAttributes = array(
        'dateFormat' => 'dd-mm-yy',
        'timeFormat' => 'HH:mm',
    );
    
    public function init()
    {
        $configManager = $this->serviceLocator->getServiceLocator()->get('configManager');
        
        if ($configManager->has('system', 'date_format') && $configManager->has('system', 'time_format')) {
            $this->format = $configManager->get('system', 'date_format') . ' ' . $configManager->get('system', 'time_format');
        }        
        if ($configManager->has('system', 'js_date_format') && $configManager->has('system', 'js_time_format')) {
            $this->jsAttributes['dateFormat'] = $configManager->get('system', 'js_date_format');
            $this->jsAttributes['timeFormat'] = $configManager->get('system', 'js_time_format');
        }
    }
    
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
    
    public function getJsAttributes()
    {
        return $this->jsAttributes;
    }
    
    public function setJsAttributes($attribs)
    {
        $this->jsAttributes = $attribs;
        return $this;
    }
    
    public function setJsAttribute($key, $val)
    {
        $this->jsAttributes[$key] = $val;
    }
    
    public function getJsAttribute()
    {
        if (isset($this->jsAttributes[$key])) {
            return $this->jsAttributes[$key];
        }
        return null;
    }
    
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['jsAttributes'])) {
            $this->setJsAttributes($this->options['jsAttributes']);
        }

        return $this;
    }
    
    public function getInputSpecification()
    {
        $self = $this;
        
        return array(
            'name' => $this->getName(),
            'required' => true,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array(
                    'name' => 'callback', 
                    'options' => array(
                        'callback' => function($val) use ($self) {
                            $format = $self->getFormat();
            
                            $dateTime = \DateTime::createFromFormat($format, $val);
                            if (false === $dateTime) {
                                return $val;
                            }
                            return $dateTime;
                        },
                        'callback_params' => array()
                    ),
                ),
            ),
            'validators' => $this->getValidators(),
        );
    }
}