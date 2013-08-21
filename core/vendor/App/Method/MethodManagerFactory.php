<?php

namespace App\Method;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

class MethodManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = 'App\Method\MethodManager';
}