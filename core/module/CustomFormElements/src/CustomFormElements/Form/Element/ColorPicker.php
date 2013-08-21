<?php

namespace CustomFormElements\Form\Element;

use Zend\Form\Element\Color;
use Zend\Validator\Regex as RegexValidator;

/**
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ColorPicker extends Color
{    
    protected $jsAttributes = array(
    );
    
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
            $this->jsAttributes = array_merge($this->jsAttributes, $this->options['jsAttributes']);
        }

        return $this;
    }
    
    /**
     * Get validator
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new RegexValidator('/^#[0-9a-fA-F]{6}$/');
        }
        return $this->validator;
    }
}