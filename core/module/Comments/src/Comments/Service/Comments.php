<?php

namespace Comments\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Comments implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $commentedObjectsTable = 'commented_objects';
    
    protected $commentsTable = 'comments';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function allowComments($objectId)
    {
        $db = $this->serviceManager->get('db');
        
        $db->query('insert ignore into ' . DB_PREF . $this->commentedObjectsTable . ' (object_id) values (?)', array($objectId));
    }
    
    public function disallowComments($objectId)
    {
        $db = $this->serviceManager->get('db');
        
        $db->query('delete from ' . DB_PREF . $this->commentedObjectsTable . ' where object_id = ?', array($objectId));
    }
    
    public function isAllowedComments($objectId)
    {
        $db = $this->serviceManager->get('db');
        
        $configManager = $this->serviceManager->get('configManager');
        
        if (!$configManager->get('comments', 'activate_standard_comments', false)) {
            return false;
        }
        
        $sqlRes = $db->query('select count(*) as cnt from ' . DB_PREF . $this->commentedObjectsTable . ' where object_id = ?', array($objectId))->toArray();
        
        return ($sqlRes[0]['cnt'] > 0);
    }
    
    public function isAllowedCommentsToUser($objectId)
    {
        if (!$this->isAllowedComments($objectId)) {
            return false;
        }
        
        $auth = $this->serviceManager->get('Rbac\Service\Authorize');
                
        return $auth->isAllowed('add_comments');
    }
    
    public function getComments($objectId)
    {   
        if ($this->isAllowedComments($objectId)) {
            $db = $this->serviceManager->get('db');
            
            $comments = $db->query('
                select * 
                from ' . DB_PREF . $this->commentsTable . ' 
                where object_id = ?', array($objectId))->toArray();
        } else {
            $comments = array();
        }
        
        return $comments;
    }
    
}