<?php


namespace Rbac\Service;

use Rbac\Provider\Identity\UsersZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \BjyAuthorize\Provider\Identity\ZfcUserZendDb}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class UsersZendDbIdentityProviderServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \BjyAuthorize\Provider\Identity\ZfcUserZendDb
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $adapter \Zend\Db\Adapter\Adapter */
        $adapter     = $serviceLocator->get('db');
        /* @var $userService \ZfcUser\Service\User */
        
        $userService = $serviceLocator->get('users_service');
        
        $provider = new UsersZendDb($adapter, $userService);

        return $provider;
    }
}
