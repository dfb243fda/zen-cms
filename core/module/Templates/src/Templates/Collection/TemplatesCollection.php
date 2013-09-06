<?php

namespace Templates\Collection;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Db\Sql\Sql;

class TemplatesCollection implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $templatesTable = 'templates';
    
    protected $markersTable = 'template_markers';
    
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function addTemplate($module, $method, $templateType, $data)
    {
        $db = $this->serviceManager->get('db');
        $config = $this->serviceManager->get('config');
        
        if ($data['is_default']) {
            $db->query('
                update ' . DB_PREF . $this->templatesTable . '
                set is_default = 0
                where module = ? and method = ?', array($module, $method));
        }
        
        $sql = new Sql($db);
        $insert = $sql->insert(DB_PREF . $this->templatesTable);
        
        $insertData = array(
            'name' => $data['name'],
            'title' => $data['title'],
            'module' => $module, 
            'method' => $method,
            'type' => $templateType,
            'is_default' => (int)$data['is_default'],
        );
        
        $insert->values($insertData);
        
        $sql->prepareStatementForSqlObject($insert)->execute();

        $templateId = $db->getDriver()->getLastGeneratedValue();
        
        $fileManager = $this->serviceManager->get('fileManager');
        
        $templateDir = APPLICATION_PATH . '/' . $config['Templates']['templatesDir'] . '/' . $templateType . '/' . $module;
        if ($method) {
            $templateDir .= '/' . $method;
        }   
        if (!is_dir($templateDir)) {
            $fileManager->mkdir($templateDir, true);
        }
        
        file_put_contents($templateDir . '/' . $data['name'], $data['content']);
        
        $markersStr = $data['markers'];
        
        $tmp = explode(LF, $markersStr);
        
        $markers = array();
        foreach ($tmp as $v) {
            $parts = explode('=', $v);
            if (2 == count($parts)) {
                $markers[] = array(
                    'name' => trim($parts[0]),
                    'title' => trim($parts[1]),
                    'template_id' => $templateId,
                );
            }
        }
        
        foreach ($markers as $v) {
            $sql = new Sql($db);
            $insert = $sql->insert(DB_PREF . $this->markersTable);
            
            $insert->values($v);
        
            $sql->prepareStatementForSqlObject($insert)->execute();
        }
        
        return $templateId;
    }
    
    public function deleteTemplate($id)
    {
        $db = $this->serviceManager->get('db');
        $config = $this->serviceManager->get('config');
        
        $sqlRes = $db->query('select * from ' . DB_PREF . $this->templatesTable . ' where id = ? limit 1', array($id))->toArray();
        
        if (empty($sqlRes)) {
            return false;
        }
        $result = $sqlRes[0];
        
        $db->query('delete from ' . DB_PREF . $this->templatesTable . ' where id = ?', array($id));
        
        $templateDir = APPLICATION_PATH . '/' . $config['Templates']['templatesDir'] . '/' . $result['type'] . '/' . $result['module'];
        if ($result['method']) {
            $templateDir .= '/' . $result['method'];
        }   
        
        if (is_file($templateDir . '/' . $result['name'])) {
            unlink($templateDir . '/' . $result['name']);
        }
        
        return true;
    }
}