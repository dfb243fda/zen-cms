<?php

namespace App\ObjectType;

use Zend\Form\Form as ZendForm;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Form extends ZendForm implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectType;
    
    protected $onlyVisible = false;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
        return $this;
    }
    
    public function setOnlyVisible($onlyVisible)
    {
        $this->onlyVisible = (bool)$onlyVisible;
    }
    
    public function create()
    {
        if (null === $this->objectType) {
            throw new \Exception('object type is undefined');
        }
        
        $this->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
        
        $translator = $this->serviceManager->get('translator');
        $onlyVisible = $this->onlyVisible;
        
        $this->add(array(
            'name' => 'common',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('App:ObjectType:Common params fields group'),
            ),
        ));
                
        $this->get('common')->add(array(
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('App:ObjectType:Name field'),
            ),
        ));
        
        
        $fieldGroups = $this->objectType->getFieldGroups();
                
        foreach ($fieldGroups as $k=>$v) {
            $fields = $v->getFields();
            
            if (!$this->has($v->getName())) {
                $this->add(array(
                    'name' => $v->getName(),
                    'type' => 'fieldset',
                    'options' => array(
                        'label' => $translator->translate($v->getTitle()),
                    ),
                ));
            }
                        
            
            $inputFilter = array();
            foreach ($fields as $k2=>$v2) {
                if ($onlyVisible && !$v2->getIsVisible()) {
                    continue;
                }
                $zendFormElement = $v2->getZendFormElement();
                $this->get($v->getName())->add($zendFormElement);

                $this->getInputFilter()->get($v->getName())->add(array(
                    'required' => $v2->getIsRequired(),
                ), $zendFormElement->getName());
            }
        }
    }
    
    public function getAppFormConfig($additionalBaseFormConfig = array(), $onlyVisible = false)
    {               
        $baseFormConfig = array(
            'fieldsets' => array(
                'common' => array(
                    'spec' => array(
                        'name' => 'common',
                        'options' => array(
                            'label' => $this->translator->translate('App:ObjectType:Common params fields group'),
                        ),
                        'elements' => array(
                            'name' => array(
                                'spec' => array(
                                    'name' => 'name',
                                    'options' => array(
                                        'label' => $this->translator->translate('App:ObjectType:Name field'),
                                        'required' => true,
                                    ),
                                    'attributes' => array(
                                        'type' => 'text',
                                    ),
                                ),
                            ),
                        ),                        
                    ),
                ),
            ),
            'input_filter' => array(),
        );    
        
        if (isset($additionalBaseFormConfig['fieldsets'])) {
            foreach ($additionalBaseFormConfig['fieldsets'] as $k=>$v) {
                if (isset($baseFormConfig['fieldsets'][$k])) {
                    $baseFormConfig['fieldsets'][$k]['spec']['elements'] = array_merge($baseFormConfig['fieldsets'][$k]['spec']['elements'], $v['spec']['elements']);                    
                } else {
                    $baseFormConfig['fieldsets'][$k] = $v;
                }
            }
        }        
        if (!empty($additionalBaseFormConfig['input_filter'])) {
            $baseFormConfig['input_filter'] = array_merge_recursive($baseFormConfig['input_filter'], $additionalBaseFormConfig['input_filter']);
        }        
        
        $fieldGroups = $this->getFieldGroups();
                
        foreach ($fieldGroups as $k=>$v) {
            $fields = $v->getFields();
            
            if (isset($baseFormConfig['fieldsets'][$v->getName()])) {
                $formElements = $baseFormConfig['fieldsets'][$v->getName()]['spec']['elements'];
            } else {
                $baseFormConfig['fieldsets'][$v->getName()] = array(
                    'spec' => array(
                        'name' => $v->getName(),
                        'options' => array(
                            'label' => $this->translator->translateI18n($v->getTitle()),
                        ),
                        'elements' => array(
                            
                        ),
                    ),
                );
                $formElements = array();
            }
            
            $inputFilter = array();
            foreach ($fields as $k2=>$v2) {
                if ($onlyVisible && !$v2->getIsVisible()) {
                    continue;
                }
                
                $formElements['field_' . $k2] = $v2->getAppFormElementConfig();
                if (isset($formElements['field_' . $k2]['input_filter'])) {
                    $inputFilter['field_' . $k2] = $formElements['field_' . $k2]['input_filter'];
                    unset($formElements['field_' . $k2]['input_filter']);
                }
            }
            
            if (!empty($inputFilter)) {
                if (!isset($baseFormConfig['input_filter'][$v->getName()])) {
                    $baseFormConfig['input_filter'][$v->getName()]['type'] = 'Zend\InputFilter\InputFilter';
                }
                $baseFormConfig['input_filter'][$v->getName()] = array_merge($baseFormConfig['input_filter'][$v->getName()], $inputFilter);
            }
            
            $baseFormConfig['fieldsets'][$v->getName()]['spec']['elements'] = $formElements;
        }
        
        return $baseFormConfig;
    }
}