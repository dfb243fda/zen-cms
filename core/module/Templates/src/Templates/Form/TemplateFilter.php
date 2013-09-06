<?php

namespace Templates\Form;

use Zend\InputFilter\InputFilter;

class TemplateFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'name',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-z_\-0-9]+\.[a-z]{3,4}$/'
                    ),
                )
            ),
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'StringToLower'),
            ),
        ));
                
        $this->add(array(
            'name' => 'title',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
        ));
    }
}