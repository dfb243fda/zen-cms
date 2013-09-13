<?php

namespace Users\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class LoginzaFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {             
        $this->setLabel('opa2');
        
//        $this->setUseAsBaseFieldset(true);
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'allow_loginza',
            'options' => array(
                'label' => 'i18n::Dynamic config allow loginza',
                'description' => 'i18n::Dynamic config allow loginza description',
            ),
        ));
        
        $this->add(array(
            'name' => 'loginza_widget_id',
            'options' => array(
                'label' => 'i18n::Dynamic config loginza_widget_id',
                'description' => 'i18n::Dynamic config loginza_widget_id description',
            ),
        ));
        
        $this->add(array(
            'name' => 'loginza_secret',
            'options' => array(
                'label' => 'i18n::Dynamic config loginza_secret',
                'description' => 'i18n::Dynamic config loginza_secret description',
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'loginza_secret_is_protected',
            'options' => array(
                'label' => 'i18n::Dynamic config loginza_secret_is_protected',
                'description' => 'i18n::Dynamic config loginza_secret_is_protected description',
            ),
        ));
    }
   
    public function getInputFilterSpecification()
    {        
        return array(
            'loginza_widget_id' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim',)
                )
            ),
            'loginza_secret' => array(
                'filters' => array(
                    array('name' => 'StringTrim',)
                )
            ),
        );
    }
}