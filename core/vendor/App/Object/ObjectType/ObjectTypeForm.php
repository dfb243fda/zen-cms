<?php

namespace App\Object\ObjectType;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ObjectTypeForm extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
                
    protected $onlyVisible;
    
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
    
    public function init()
    {
        $objectType = $this->getOption('objectType');
        
        $onlyVisible = $this->getOption('onlyVisible');
        if (null === $onlyVisible) {
            $onlyVisible = $this->onlyVisible;
        }
        
        
        if (null === $objectType) {
            throw new \Exception('object type is undefined');
        }
                
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');        

/*        
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
*/        
        
        $fieldGroups = $objectType->getFieldGroups();
                
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
            }
            
            foreach ($fields as $k2=>$v2) {
                if ($onlyVisible && !$v2->getIsVisible()) {
                    continue;
                }
                $zendFormElement = $v2->getZendFormElement();

      //          $this->getInputFilter()->get($v->getName())->add(array(
        //            'required' => $v2->getIsRequired(),
          //      ), $zendFormElement->getName());
            }
        }
    }
}