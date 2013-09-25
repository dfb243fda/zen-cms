<?php

namespace Users\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class RegistrationBaseForm extends Form implements ServiceLocatorAwareInterface
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
        $translator = $rootServiceManager->get('translator');
        $config = $rootServiceManager->get('config');
        $usersConfig = $config['Users'];

        $this->add(array(
            'name' => 'common',
            'type' => 'fieldset',
        ));

        if ($usersConfig['enableLogin']) {
            $this->get('common')->add(array(
                'name' => 'login',
                'options' => array(
                    'label' => $translator->translate('Users:Login field'),
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



        if ($usersConfig['enableLogin']) {
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
                    'key' => 'login'
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
                'key' => 'email'
            ));
    }
}