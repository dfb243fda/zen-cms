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
                        'label' => $translator->translateI18n($v->getTitle()),
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
}