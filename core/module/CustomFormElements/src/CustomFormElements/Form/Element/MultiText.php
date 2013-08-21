<?php

namespace CustomFormElements\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Filter\Callback as CallbackFilter;

class MultiText extends Element implements InputProviderInterface
{
    protected $attributes = array(
        'type' => 'text',
    );
    
    protected $filter;
        
    public function init()
    {
        if (null === $this->getAttribute('id')) {
            $this->setAttribute('id', 'id_multitext_' . md5(microtime(true)));
        }
    }
    
    protected function getFilter()
    {
        if (null === $this->filter) {
            $filter = new CallbackFilter(function($values) {
                if (!is_array($values)) {
                    return $values;
                }
                
                foreach ($values as $k=>$value) {
                    if ('' == $value) {
                        unset($values[$k]);
                    }
                }
                return $values;
            });

            $this->filter = $filter;
        }
        return $this->filter;
    }
    
    /**
     * Provide default input rules for this element
     *
     * Attaches the captcha as a validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $spec = array(
            'name' => $this->getName(),
            'required' => true,
        );
        
        $spec['filters'] = array(
            $this->getFilter(),
        );

        return $spec;
    }
}