<?php
/**
 * This file is part of the DBSessionStorage Module (https://github.com/Nitecon/DBSessionStorage.git)
 *
 * Copyright (c) 2013 Will Hattingh (https://github.com/Nitecon/DBSessionStorage.git)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */
namespace DBSessionStorage;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class DBStorage  {
        
    protected $tblGW;
    
    protected $sessionConfig;
    
    public function __construct($adapter, $dbPref, $sessionConfig) {        
        $this->tblGW = new \Zend\Db\TableGateway\TableGateway($dbPref . 'sessions', $adapter);
        
        $this->sessionConfig = $sessionConfig;
    }
    public function setSessionStorage($request)
    {        
        $gwOpts = new DbTableGatewayOptions();
        $gwOpts->setDataColumn('data');
        $gwOpts->setIdColumn('id');
        $gwOpts->setLifetimeColumn('lifetime');
        $gwOpts->setModifiedColumn('modified');
        $gwOpts->setNameColumn('name');

        $saveHandler = new DbTableGateway($this->tblGW, $gwOpts);
        
        $sessionConfig = array_merge(array(
            'cookie_path' => $request->getBasePath() . '/',
        ), $this->sessionConfig);
                
        $config = new \Zend\Session\Config\SessionConfig();
        $config->setOptions($sessionConfig);
        $sessionManager = new SessionManager($config);
        
        $sessionManager->setSaveHandler($saveHandler);
        
        Container::setDefaultManager($sessionManager);
//        $sessionManager->start();
    }

}