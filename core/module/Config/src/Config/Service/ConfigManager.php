<?php

namespace Config\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ConfigManager implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $initialized = false;
    
    protected $db;
    protected $configTable = 'config';
    protected $config;
    
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    public function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->db = $this->serviceManager->get('db');
            
            $this->translator = $this->serviceManager->get('translator');
  
            $sqlRes = $this->db->query('
                select entry_namespace, entry_key, entry_value
                from ' . DB_PREF . $this->configTable, array())->toArray();
            
            $this->config = array();
            foreach ($sqlRes as $row) {
                $this->config[$row['entry_namespace']][$row['entry_key']] = $row['entry_value'];
            }
        }
    }
    
    public function get($namespace, $key, $default=null)
    {
        $this->init();
        
        if (isset($this->config[$namespace][$key])) {
            if (!isset($this->unserializedConfig[$namespace][$key])) {
                $this->unserializedConfig[$namespace][$key] = true;
                $this->config[$namespace][$key] = unserialize($this->config[$namespace][$key]);
            }
            return $this->config[$namespace][$key];
        }
        else {
            return $default;
        }
    }

    public function set($namespace, $key, $value)
    {
        $this->init();
        
        $serialized_value = serialize($value);

        try {
            if (isset($this->config[$namespace][$key])) {
                $this->db->query('
                    update ' . DB_PREF . $this->configTable . '
                    set entry_value = ?
                    where entry_namespace = ? and entry_key = ?', array($serialized_value, $namespace, $key));                
            }
            else {
                $this->db->query('
                    insert into ' . DB_PREF . $this->configTable . '
                    (entry_namespace, entry_key, entry_value)
                    values (?, ?, ?)', array($namespace, $key, $serialized_value));
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return false;
        }

        $this->unserializedConfig[$namespace][$key] = true;
        $this->config[$namespace][$key] = $value;
        return true;
    }
    
    

    public function has($namespace, $key)
    {
        $this->init();
        return isset($this->config[$namespace][$key]);
    }
}