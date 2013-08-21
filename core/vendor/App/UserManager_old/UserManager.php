<?php

namespace App\UserManager;

class UserManager
{

    protected $serviceManager;
    
    protected $_bootstrap = null;
    protected $_db = null;
    protected $_usersTable = 'users';
    protected $_rolesTable = 'roles';
    protected $_userIdentity = null;
    protected $_userData = null;

    public function __construct($options)
    {
        $this->setOptions($options);

        if (null === $this->_db) {
            $this->_db = $this->serviceManager->get('db');
        }
    }

    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . strtolower($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    
    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }

    public function isAuth()
    {
        return !empty($this->_userData['role']);
    }

    public function setUserIdentity($id)
    {
        $this->_userIdentity = $id;
        return $this;
    }

    public function init()
    {
        if (null === $this->_userData) {
            if (null === $this->_userIdentity) {
                $this->_userData = array(
                    'role' => array(),
                    'admin' => 0,
                );
            } else {
                $sqlRes = $this->_db
                        ->query('
                            SELECT t1.*, IF (SUM(t2.admin)  > 0, 1, 0) AS admin
                            FROM `' . DB_PREF . $this->_usersTable . '` t1
                                LEFT JOIN `' . DB_PREF . $this->_rolesTable . '` t2
                                    ON t1.role=t2.id OR
                                        t1.role LIKE CONCAT("%,", t2.id, ",%") OR 
                                        t1.role LIKE CONCAT(t2.id, ",%") OR 
                                        t1.role LIKE CONCAT("%,", t2.id)
                            WHERE t1.login=?
                        ', array($this->_userIdentity))
                        ->fetchAll();

                if (empty($sqlRes)) {
                    $this->_userData = array(
                        'role' => array(),
                        'admin' => 0,
                    );
                } else {
                    if ($sqlRes[0]['role'] == '') {
                        $sqlRes[0]['role'] = array();
                    } else {
                        $sqlRes[0]['role'] = explode(',', $sqlRes[0]['role']);
                    }

                    $this->_userData = $sqlRes[0];
                }
            }
        }
    }

    public function getId()
    {
        return $this->getUserData('id');
    }
    
    public function getUserData($elem = null)
    {
        if (null === $elem) {
            return $this->_userData;
        } else {
            if (isset($this->_userData[$elem])) {
                return $this->_userData[$elem];
            } else {
                return null;
            }
        }
    }

}