<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Rbac\Service;

use Rbac\Provider\Role\ProviderInterface as RoleProvider;
use Rbac\Provider\Resource\ProviderInterface as ResourceProvider;
use Rbac\Provider\Rule\ProviderInterface as RuleProvider;
use Rbac\Provider\Identity\ProviderInterface as IdentityProvider;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Exception\InvalidArgumentException;
use Zend\Permissions\Acl\Resource\GenericResource;
use Rbac\Guard\GuardInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\ServiceManager\ServiceManager;

class Authorize
{
    const TYPE_ALLOW = 'allow';

    const TYPE_DENY = 'deny';

    /**
     * @var Acl
     */
    protected $acl;

    /**
     * @var RoleProvider[]
     */
    protected $roleProviders = array();

    /**
     * @var ResourceProvider[]
     */
    protected $resourceProviders = array();

    /**
     * @var RuleProvider[]
     */
    protected $ruleProviders = array();

    /**
     * @var IdentityProvider
     */
    protected $identityProvider;

    /**
     * @var GuardInterface[]
     */
    protected $guards = array();

    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * {@inheritDoc}
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->load();
    }
    

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param RoleProvider $provider
     *
     * @return self
     */
    public function addRoleProvider(RoleProvider $provider)
    {
        $this->roleProviders[] = $provider;

        return $this;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param ResourceProvider $provider
     *
     * @return self
     */
    public function addResourceProvider(ResourceProvider $provider)
    {
        $this->resourceProviders[] = $provider;

        return $this;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param RuleProvider $provider
     *
     * @return self
     */
    public function addRuleProvider(RuleProvider $provider)
    {
        $this->ruleProviders[] = $provider;

        return $this;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param IdentityProvider $provider
     *
     * @return self
     */
    public function setIdentityProvider(IdentityProvider $provider)
    {
        $this->identityProvider = $provider;

        return $this;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @return IdentityProvider
     */
    public function getIdentityProvider()
    {
        return $this->identityProvider;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param GuardInterface $guard
     *
     * @return self
     */
    public function addGuard(GuardInterface $guard)
    {
        $this->guards[] = $guard;

        if ($guard instanceof ResourceProvider) {
            $this->addResourceProvider($guard);
        }

        if ($guard instanceof RuleProvider) {
            $this->addRuleProvider($guard);
        }

        return $this;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 1.4.x+,
     *             please retrieve the guards from the `BjyAuthorize\Guards` service
     *
     * @return GuardInterface[]
     */
    public function getGuards()
    {
        return $this->guards;
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 1.4.x+,
     *             please retrieve the identity from the
     *             `BjyAuthorize\Provider\Identity\ProviderInterface` service
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'rbac-identity';
    }

    /**
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param string|ResourceInterface $resource
     * @param string                   $privilege
     *
     * @return bool
     */
    public function isAllowed($resource, $privilege = null)
    {        
        try {
            return $this->acl->isAllowed($this->getIdentity(), $resource, $privilege);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Initializes the service
     *
     * @internal
     */
    public function load()
    {
        $this->acl    = new Acl();

        foreach ($this->serviceManager->get('Rbac\RoleProviders') as $provider) {
            $this->addRoleProvider($provider);
        }

        foreach ($this->serviceManager->get('Rbac\ResourceProviders') as $provider) {
            $this->addResourceProvider($provider);
        }

        foreach ($this->serviceManager->get('Rbac\RuleProviders') as $provider) {
            $this->addRuleProvider($provider);
        }

        $config = $this->serviceManager->get('config');
        $this->setIdentityProvider($this->serviceManager->get($config['Rbac']['identity_provider']));

        foreach ($this->serviceManager->get('Rbac\Guards') as $guard) {
            $this->addGuard($guard);
        }

        foreach ($this->roleProviders as $provider) {
            $this->addRoles($provider->getRoles());
        }

        foreach ($this->resourceProviders as $provider) {
            $this->loadResource($provider->getResources(), null);
        }

        foreach ($this->ruleProviders as $provider) {
            $rules = $provider->getRules();
            if (isset($rules['allow'])) {
                foreach ($rules['allow'] as $rule) {
                    $this->loadRule($rule, static::TYPE_ALLOW);
                }
            }

            if (isset($rules['deny'])) {
                foreach ($rules['deny'] as $rule) {
                    $this->loadRule($rule, static::TYPE_DENY);
                }
            }
        }

        $parentRoles = $this->getIdentityProvider()->getIdentityRoles();

        $this->acl->addRole($this->getIdentity(), $parentRoles);
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param \Zend\Permissions\Acl\Role\RoleInterface[] $roles
     */
    protected function addRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        /* @var $role Role */
        foreach ($roles as $role) {
            if ($this->acl->hasRole($role)) {
                continue;
            }

            if ($role->getParent() !== null) {
                $this->addRoles(array($role->getParent()));                
                $this->acl->addRole($role, $role->getParent());
            } else {
                $this->acl->addRole($role);
            }
        }
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param string[]|\Zend\Permissions\Acl\Resource\ResourceInterface[] $resources
     * @param mixed|null                                                  $parent
     */
    protected function loadResource(array $resources, $parent = null)
    {
        foreach ($resources as $key => $value) {
            if (is_string($key)) {
                $key = new GenericResource($key);
            } elseif (is_int($key)) {
                $key = new GenericResource($value);
            }

            if (is_array($value)) {
                $this->acl->addResource($key, $parent);
                $this->loadResource($value, $key);
            } else {
                $this->acl->addResource($key, $parent);
            }
        }
    }

    /**
     * @deprecated this method will be removed in BjyAuthorize 2.0.x
     *
     * @param mixed $rule
     * @param mixed $type
     *
     * @throws \InvalidArgumentException
     */
    protected function loadRule(array $rule, $type)
    {
        $privileges = $assertion = null;
        $ruleSize   = count($rule);

        if (4 === $ruleSize) {
            list($roles, $resources, $privileges, $assertion) = $rule;
            $assertion = $this->serviceManager->get($assertion);
        } elseif (3 === $ruleSize) {
            list($roles, $resources, $privileges) = $rule;
        } elseif (2 === $ruleSize) {
            list($roles, $resources) = $rule;
        } else {
            throw new \InvalidArgumentException('Invalid rule definition: ' . print_r($rule, true));
        }

        if (is_string($assertion)) {
            $assertion = $this->serviceManager->get($assertion);
        }

        if (static::TYPE_ALLOW === $type) {
            $this->acl->allow($roles, $resources, $privileges, $assertion);
        } else {
            $this->acl->deny($roles, $resources, $privileges, $assertion);
        }
    }
}
