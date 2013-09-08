<?php

namespace Users\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class RegistrationForm extends Form implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    
    protected $onlyVisible = false;
    
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
        $rootServiceManager = $this->serviceLocator->getServiceLocator();
        $objectTypesCollection = $rootServiceManager->get('objectTypesCollection');
        $db = $rootServiceManager->get('db');
        $translator = $rootServiceManager->get('translator');
        $config = $rootServiceManager->get('config');                       
        $usersConfig = $config['Users'];
        
        $objectTypeId = $this->getOption('objectTypeId');
        
        $onlyVisible = $this->getOption('onlyVisible');
        if (null === $onlyVisible) {
            $onlyVisible = $this->onlyVisible;
        }
        
        
        $sqlRes = $db->query('
            select id, name 
            from ' . DB_PREF . 'roles', array())->toArray();
        
        $roles = array();
        foreach ($sqlRes as $row) {
            $roles[$row['id']] = $row['name'];
        }
        
        $this->add(array(
            'name' => 'common',
            'type' => 'fieldset',
        ));
        
        if ($usersConfig['enableUserName']) {
            $this->get('common')->add(array(
                'name' => 'user_name',
                'options' => array(
                    'label' => $translator->translate('Users:Username field'),
                ),
            ));
        }
        if ($usersConfig['enableDisplayName']) {
            $this->get('common')->add(array(
                'name' => 'email',
                'options' => array(
                    'label' => $translator->translate('Users:Email field'),
                ),
            ));
        }
        
        $this->get('common')->add(array(
                'name' => 'display_name',
                'options' => array(
                    'label' => $translator->translate('Users:Display name field'),                            
                ),
            ))
            ->add(array(
                'name' => 'password',
                'options' => array(
                    'label' => $translator->translate('Users:Password field'),
                ),
                'attributes' => array(
                    'type' => 'password'
                ),
            ))
            ->add(array(
                'name' => 'passwordVerify',
                'options' => array(
                    'label' => $translator->translate('Users:Password verify field'),
                ),
                'attributes' => array(
                    'type' => 'password'
                ),
            ));
        
        
        
        if ($usersConfig['enableUserName']) {
            $this->getInputFilter()->get('common')->get('user_name')
                                                  ->setRequired(true);

            $this->getInputFilter()->get('common')->get('user_name')
                                                  ->getValidatorChain()
                                                  ->attachByName('StringLength', array(
                                                      'min' => 3,
                                                      'max' => 255,
                                                  ))
                                                  ->attachByName('Users\Validator\NoRecordExists', array(
                                                      'usersCollection' => $rootServiceManager->get('Users\Collection\Users'),
                                                      'key' => 'user_name'
                                                  ));
        }
        
        if ($usersConfig['enableDisplayName']) {
            $this->getInputFilter()->get('common')->get('display_name')
                                                ->setRequired(false);

          $this->getInputFilter()->get('common')->get('display_name')
                                                ->getFilterChain()
                                                ->attachByName('StringTrim');

          $this->getInputFilter()->get('common')->get('display_name')
                                                ->getValidatorChain()
                                                ->attachByName('StringLength', array(
                                                    'min' => 3,
                                                    'max' => 128,
                                                ));           
        }        
        
        $this->getInputFilter()->get('common')->get('password')
                                              ->setRequired(true);
        
        $this->getInputFilter()->get('common')->get('password')
                                              ->getValidatorChain()
                                              ->attachByName('StringLength', array(
                                                  'min' => 6,
                                              ));
        
        
        $this->getInputFilter()->get('common')->get('passwordVerify')
                                              ->setRequired(true);
        
        $this->getInputFilter()->get('common')->get('passwordVerify')
                                              ->getValidatorChain()
                                              ->attachByName('StringLength', array(
                                                  'min' => 6,
                                              ))
                                              ->attachByName('Identical', array(
                                                  'token' => 'password',
                                              ));
        
        $this->getInputFilter()->get('common')->get('email')
                                              ->setRequired(true);
        
        $this->getInputFilter()->get('common')->get('email')
                                              ->getValidatorChain()
                                              ->attachByName('EmailAddress')
                                              ->attachByName('Users\Validator\NoRecordExists', array(
                                                  'usersCollection' => $rootServiceManager->get('Users\Collection\Users'),
                                                  'key'    => 'email'
                                              ));
        
        if (null !== $objectTypeId) {
            $objectType = $objectTypesCollection->getType($objectTypeId);                    
            $this->mergeForms($objectType->getForm($onlyVisible));
        }
    }
    
    protected function mergeForms(\Zend\Form\Form $form2)
    {
        $form1 = $this;
        
        foreach ($form2->getFieldsets() as $fieldset) {
            if ($form1->has($fieldset->getName())) {
                foreach ($fieldset->getElements() as $element) {
                    if (!$form1->get($fieldset->getName())->has($element->getName())) {
                        $form1->get($fieldset->getName())->add($element);
                    }                    
                }                
            } else {
                $form1->add($fieldset);
            }            
        }
        
        foreach ($form2->getInputFilter()->getInputs() as $inputFilterKey=>$inputFilter) {            
            if (!$form1->getInputFilter()->has($inputFilterKey)) {                
                $form1->getInputFilter()->add($inputFilter, $inputFilterKey);                
            } else {
                foreach ($inputFilter->getInputs() as $inputKey=>$input) {
                    $form1->getInputFilter()->get($inputFilterKey)->add($input, $inputKey);
                }  
            }                      
        }      
    }
}