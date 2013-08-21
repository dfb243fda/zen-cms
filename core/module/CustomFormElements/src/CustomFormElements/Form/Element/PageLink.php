<?php

namespace CustomFormElements\Form\Element;

use Zend\Form\Element\Select;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class PageLink extends Select implements ServiceLocatorAwareInterface
{    
    public function init()
	{        
		if (empty($this->valueOptions)) {
			$sm = $this->serviceLocator->getServiceLocator();
		
            $db = $sm->get('db');
            
			$sqlRes = $db->query('SELECT t1.id,
                (SELECT t2.name FROM ' . DB_PREF . 'objects t2 WHERE t2.id = t1.object_id) AS name 
                FROM ' . DB_PREF . 'pages t1 WHERE t1.is_deleted = 0 ORDER BY t1.sorting', array())->toArray();
		
			$multiOptions = array();
			foreach ($sqlRes as $row) {
				$multiOptions[$row['id']] = $row['name'];
			}
		
			$this->setValueOptions($multiOptions);
		}
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}
    
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
	
}