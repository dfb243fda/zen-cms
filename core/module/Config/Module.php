<?php

namespace Config;

class Module
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
    
    public function getDynamicConfig($sm)
    {   
        $fieldset = new \Zend\Form\Fieldset('loginza');
     
        $formElementManager = $sm->get('FormElementManager');
        
        $fieldset->getFormFactory()->setFormElementManager($formElementManager);
     
        $fieldset->setLabel('opa2');
        
        $fieldset->add(array(
            'type' => 'checkbox',
            'name' => 'allow_loginza',
            'options' => array(
                'label' => 'i18n::Dynamic config allow loginza',
                'description' => 'i18n::Dynamic config allow loginza description',
            ),
        ));
        
        $fieldset->add(array(
            'name' => 'loginza_widget_id',
            'options' => array(
                'label' => 'i18n::Dynamic config loginza_widget_id',
                'description' => 'i18n::Dynamic config loginza_widget_id description',
            ),
        ));
        
        $fieldset->add(array(
            'name' => 'loginza_secret',
            'options' => array(
                'label' => 'i18n::Dynamic config loginza_secret',
                'description' => 'i18n::Dynamic config loginza_secret description',
            ),
        ));
        
        $fieldset->add(array(
            'type' => 'checkbox',
            'name' => 'loginza_secret_is_protected',
            'options' => array(
                'label' => 'i18n::Dynamic config loginza_secret_is_protected',
                'description' => 'i18n::Dynamic config loginza_secret_is_protected description',
            ),
        ));
        
        
        
        if (!$formElementManager->has('loginzaFieldset')) {
            $formElementManager->setService('loginzaFieldset', $fieldset);
        }
        
        
        
        return array(
            'form' => array(
                'loginza' => array(
                    'fieldsets' => array(                    
                        array(
                            'spec' => array(
                                'name' => 'loginza',
                                'options' => array(
                                    'label' => 'opa',
                                ),
                                'elements' => array(
                                    array(
                                        'spec' => array(
                                            'type' => 'Zend\Form\Element\Collection',
                                            'name' => 'domains',
                                            'options' => array(
                                                'count' => 2,
                                                'should_create_template' => true,
                                                'allow_add' => true,
                                                'target_element' => array(
                                                    'type' => 'loginzaFieldset'
                                                )
                                            )
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
    /*                'input_filter' => array(
                        'loginza' => array(
                            'type' => 'Zend\InputFilter\InputFilter',
                            'loginza_widget_id' => array(
                                'filters' => array(
                                    array('name' => 'StringTrim',)
                                )
                            ),
                            'loginza_secret' => array(
                                'filters' => array(
                                    array('name' => 'StringTrim',)
                                )
                            ),
                        ),
                    ),
                    */
                ),
            ),
        );
    }
}