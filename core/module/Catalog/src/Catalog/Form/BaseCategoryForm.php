<?php

namespace Catalog\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BaseCategoryForm extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    
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
        $serviceManager = $this->serviceLocator->getServiceLocator();
        
        $catalogService = $serviceManager->get('Catalog\Service\Catalog');
        $objectTypesCollection = $serviceManager->get('objectTypesCollection');
        
        $typeIds = $catalogService->getCategoryTypeIds();
        
        $objectTypesMultiOptions = array();
        foreach ($typeIds as $id) {
            $objectType = $objectTypesCollection->getType($id);
            $objectTypesMultiOptions[$id] = $objectType->getName();
        }     
        
        $this->add(array(
            'type' => 'fieldset',
            'name' => 'common',
        ));
        
        $this->get('common')->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Catalog:Category name field',
            ),
        ));
        
        $this->get('common')->add(array(
            'type' => 'ObjectTypeLink',
            'name' => 'type_id',
            'options' => array(
                'label' => 'Data type',
                'value_options' => $objectTypesMultiOptions,
            ),
            'attributes' => array(
                'id' => 'object_type_id',
            ),
        ));
        
        $this->getInputFilter()->get('common')->get('type_id')->setRequired(true);
    }
}