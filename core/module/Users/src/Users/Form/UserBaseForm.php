<?php

namespace Users\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class UserBaseForm extends Form implements ServiceLocatorAwareInterface
{
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
        $rootServiceManager = $this->serviceLocator->getServiceLocator();
        $objectTypesCollection = $rootServiceManager->get('objectTypesCollection');
        $db = $rootServiceManager->get('db');
        $translator = $rootServiceManager->get('translator');
        $config = $rootServiceManager->get('config');
        $usersConfig = $config['Users'];
        $usersService = $rootServiceManager->get('Users\Service\Users');
        
        $userId = $this->getOption('userId');

        if ($userId) {
            $passRequired = false;
        } else {
            $passRequired = true;
        }
        
        $sqlRes = $db->query('
            select id, name 
            from ' . DB_PREF . 'roles', array())->toArray();

        $roles = array();
        foreach ($sqlRes as $row) {
            $roles[$row['id']] = $row['name'];
        }
        
        $typeIds = $usersService->getTypeIds();
        
        $objectTypesMultiOptions = array();
        foreach ($typeIds as $id) {
            $objectType = $objectTypesCollection->getType($id);
            $objectTypesMultiOptions[$id] = $objectType->getName();
        }     

        $this->add(array(
            'name' => 'common',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('Users:common fields group'),
            ),
        ));

        $this->get('common')->add(array(
            'name' => 'login',
            'options' => array(
                'label' => $translator->translate('Users:Login field'),
            ),
        ));

        $this->get('common')->add(array(
            'name' => 'email',
            'options' => array(
                'label' => $translator->translate('Users:Email field'),
            ),
        ));
        
        $this->get('common')->add(array(
            'name' => 'roles',
            'type' => 'select',
            'options' => array(
                'label' => $translator->translate('Users:Roles field'),
                'value_options' => $roles,
            ),
            'attributes' => array(
                'multiple' => true
            ),
        ));
        
        $this->get('common')->add(array(
            'type' => 'ObjectTypeLink',
            'name' => 'object_type_id',
            'options' => array(
                'label' => 'Data type',
                'value_options' => $objectTypesMultiOptions,
            ),
            'attributes' => array(
                'id' => 'object_type_id',
            ),
        ));
        

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


        $this->getInputFilter()->get('common')->get('login')
            ->setRequired(true);

        $this->getInputFilter()->get('common')->get('login')
            ->getValidatorChain()
            ->attachByName('StringLength', array(
                'min' => 3,
                'max' => 255,
            ))
            ->attachByName('Users\Validator\NoRecordExists', array(
                'usersCollection' => $rootServiceManager->get('Users\Collection\Users'),
                'key' => 'login',
                'exclusionUserId' => $userId,
            ));

        
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

        $this->getInputFilter()->get('common')->get('password')
            ->setRequired($passRequired);

        $this->getInputFilter()->get('common')->get('password')
            ->getValidatorChain()
            ->attachByName('StringLength', array(
                'min' => 6,
            ));


        $this->getInputFilter()->get('common')->get('passwordVerify')
            ->setRequired($passRequired);

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
                'key' => 'email',
                'exclusionUserId' => $userId,
            ));
        
        $this->getInputFilter()->get('common')->get('object_type_id')->setRequired(true);
    }
}