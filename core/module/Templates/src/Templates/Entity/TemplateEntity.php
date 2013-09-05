<?php

namespace Templates\Entity;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;


class TemplateEntity implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $templateId;
    
    protected $templatesTable = 'templates';
    
    protected $markersTable = 'template_markers';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
        return $this;
    }
    
    public function getData()
    {
        $db = $this->serviceManager->get('db');
        $id = $this->templateId;
        $config = $this->serviceManager->get('config');
        
        $sqlRes = $db->query('
            select * from ' . DB_PREF . $this->templatesTable . ' 
            where id = ? 
            limit 1', array($id))->toArray();
        
        if (empty($sqlRes)) {
            throw new \Exception('template ' . $id . ' not found');
        }
        
        $result = $sqlRes[0];
        
        $sqlRes = $db->query('
            select name, title 
            from ' . DB_PREF . $this->markersTable . ' 
            where template_id = ?', array($id))->toArray();
        
        $fileManager = $this->serviceManager->get('fileManager');
    
        $templateDir = ROOT_PATH . '/' . $config['Templates']['templatesDir'] . '/' . $result['type'] . '/' . $result['module'];
        if ($result['method']) {
            $templateDir .= '/' . $result['method'];
        }   
                
        if (!is_dir($templateDir)) {
            $fileManager->mkdir($templateDir, true);
        }
        
        if (is_file($templateDir . '/' . $result['name'])) {
            $result['content'] = file_get_contents($templateDir . '/' . $result['name']);
        } else {
            $result['content'] = '';
        }
        
        
        $result['markers'] = array();
        foreach ($sqlRes as $row) {
            $result['markers'][$row['name']] = $row['title'];
        }
        
        return $result;
    }
    
}