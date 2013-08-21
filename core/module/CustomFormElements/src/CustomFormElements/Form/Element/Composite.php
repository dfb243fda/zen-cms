<?php

namespace CustomFormElements\Form\Element;

use Zend\Form\Element\Select;
use Zend\InputFilter\InputProviderInterface;
use Zend\Filter\Callback as CallbackFilter;

class Composite extends Select  implements InputProviderInterface
{    
    protected $filter;
        
    public function init()
    {
        if (null === $this->getAttribute('id')) {
            $this->setAttribute('id', 'id_optional_' . md5(microtime(true)));
        }
    }
    
    protected function getFilter()
    {
        if (null === $this->filter) {
            $filter = new CallbackFilter(function($values) {
                if (!is_array($values)) {
                    return $values;
                }
                
                if (!empty($values['object_rel']) && !empty($values['varchar'])) {
                    foreach ($values['object_rel'] as $k=>$v) {
                        if (isset($values['varchar'][$k])) {
                            if ($v == '' && $values['varchar'][$k] == '') {
                                unset($values['object_rel'][$k]);
                                unset($values['varchar'][$k]);
                            }
                        }
                    }
                    $values['object_rel'] = array_values($values['object_rel']);
                    $values['varchar'] = array_values($values['varchar']);
                }
                
                if (empty($values['object_rel'])) {
                    $values = array();
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