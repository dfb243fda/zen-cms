<?php

namespace Config\Service;

class ConfigManager
{    
    protected $configTable = 'config';
    
    protected $config = array();    
    
    protected $unserializedConfig = array();
    
    protected $db;
    
    public function __construct($db)
    {
        $this->db = $db;
        
        $sqlRes = $this->db->query('
                select entry_namespace, entry_key, entry_value
                from ' . DB_PREF . $this->configTable, array())->toArray();
        
        foreach ($sqlRes as $row) {
            $this->config[$row['entry_namespace']][$row['entry_key']] = $row['entry_value'];
        }
    }
    
    public function get($namespace, $key, $default=null)
    {        
        if (isset($this->config[$namespace]) && array_key_exists($key, $this->config[$namespace])) {
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
        $serialized_value = serialize($value);
        
        if (isset($this->config[$namespace]) && array_key_exists($key, $this->config[$namespace])) {
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

        $this->unserializedConfig[$namespace][$key] = true;
        $this->config[$namespace][$key] = $value;
        return true;
    }
    
    

    public function has($namespace, $key)
    {
        return (isset($this->config[$namespace]) && array_key_exists($key, $this->config[$namespace]));
    }
}