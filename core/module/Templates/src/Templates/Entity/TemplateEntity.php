<?php

namespace Templates\Entity;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Db\Sql\Sql;

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
    
        $templateDir = APPLICATION_PATH . '/' . $config['Templates']['templatesDir'] . '/' . $result['type'] . '/' . $result['module'];
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
    
    public function editTemplate($data)
    {
        $templateData = $this->getData();
        $db = $this->serviceManager->get('db');
        $config = $this->serviceManager->get('config');
        
        $module = $templateData['module'];
        $method = $templateData['method'];
        
        if ($data['is_default']) {
            $db->query('
                update ' . DB_PREF . $this->templatesTable . '
                set is_default = 0
                where module = ? and method = ?', array($module, $method));
        }
        
        $fileManager = $this->serviceManager->get('fileManager');
        
        $templateDir = APPLICATION_PATH . '/' . $config['Templates']['templatesDir'] . '/' . $templateData['type'] . '/' . $module;
        if ($method) {
            $templateDir .= '/' . $method;
        }   
        if (!is_dir($templateDir)) {
            $fileManager->mkdir($templateDir, true);
        }
        
        if ($templateData['name'] != $data['name']) {
            if (is_file($templateDir . '/' . $templateData['name'])) {
                unlink($templateDir . '/' . $templateData['name']);
            }
        }
        
        file_put_contents($templateDir . '/' . $data['name'], $data['content']);
                
        unset($data['content']);
        
        if (isset($data['markers'])) {
            $markersStr = $data['markers'];
            unset($data['markers']);
        } else {
            $markersStr = '';
        }
        
        
        $sql = new Sql($db);
        $update = $sql->update(DB_PREF . $this->templatesTable);
               
        $update->set($data)->where('id = ' . (int)$this->templateId);
        
        $sql->prepareStatementForSqlObject($update)->execute();
        
        
        
        $tmp = explode(LF, $markersStr);
        
        $newMarkers = array();
        foreach ($tmp as $v) {
            $parts = explode('=', $v);
            if (2 == count($parts)) {
                $newMarkers[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        $currentMarkers = $templateData['markers'];
        
        foreach ($currentMarkers as $k=>$v) {
            if (isset($newMarkers[$k])) {
                unset($newMarkers[$k]);
            } else {
                $db->query('delete from ' . DB_PREF . $this->markersTable . ' where name = ? and template_id = ?', array($k, $this->templateId));
            }
        }
        
        foreach ($newMarkers as $k=>$v) {
            $db->query('insert into ' . DB_PREF . $this->markersTable . ' (name, title, template_id) values (?, ?, ?)', array($k, $v, $this->templateId));
        }
        
        return true;
    }
    
}