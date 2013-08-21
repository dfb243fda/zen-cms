<?php

namespace Templates\Model;

use Zend\Db\Sql\Sql;

class Templates
{
    protected $templatesTable = 'templates';
    
    protected $markersTable = 'template_markers';
    
    protected $serviceManager;
    
    protected $db;
    
    protected $translator;
    
    public function __construct($sm, $urlHelper)
    {
        $this->serviceManager = $sm;
        
        $this->db = $this->serviceManager->get('db');
        $this->moduleManager = $this->serviceManager->get('moduleManager');
        $this->urlHelper = $urlHelper;
        $this->translator = $this->serviceManager->get('translator');
        
        $this->coreConfig = $sm->get('config');
    }
    
    public function getAllTemplates()
    {        
        $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->templatesTable, array())->toArray();
        
        $templates = array();
        foreach ($sqlRes as $row) {
            $sqlRes2 = $this->db->query('
                select * 
                from ' . DB_PREF . $this->markersTable . '
                where template_id = ?', array($row['id']))->toArray();
            
            $row['markers'] = array();
            foreach ($sqlRes2 as $row2) {
                $row['markers'][] = $row2;
            }
            
            $row['showLink'] = $this->urlHelper->fromRoute('admin/method', array(
                'module' => 'TemplatesList',
                'method' => 'SingleTemplate',
                'id' => $row['id'],
            ));
            
            $row['delLink'] = $this->urlHelper->fromRoute('admin/method', array(
                'module' => 'TemplatesList',
                'method' => 'DeleteTemplate',
                'id' => $row['id'],
            ));
            
            $templates[$row['module']][$row['method']][] = $row;
        }   
        return $templates;
    }

    public function getModuleTemplates($module)
    {
        $sqlRes = $this->db->query('
            select * 
            from ' . DB_PREF . $this->templatesTable . '
            where module = ?', array($module))->toArray();
        
        $templates = array();
        foreach ($sqlRes as $row) {
            $sqlRes2 = $this->db->query('
                select * 
                from ' . DB_PREF . $this->markersTable . '
                where template_id = ?', array($row['id']))->toArray();
            
            $row['markers'] = array();
            foreach ($sqlRes2 as $row2) {
                $row['markers'][] = $row2;
            }
            
            $row['showLink'] = $this->urlHelper->fromRoute('admin/method', array(
                'module' => 'TemplatesList',
                'method' => 'SingleTemplate',
                'id' => $row['id'],
            ));
            
            $row['delLink'] = $this->urlHelper->fromRoute('admin/method', array(
                'module' => 'TemplatesList',
                'method' => 'DeleteTemplate',
                'id' => $row['id'],
            ));
            
            $templates[$row['method']][] = $row;
        }   
        return $templates;
    }
    
    public function getMethodTemplates($module, $method)
    {
        $sqlRes = $this->db->query('
            select * 
            from ' . DB_PREF . $this->templatesTable . '
            where module = ? AND method = ?', array($module, $method))->toArray();
        
        $templates = array();
        foreach ($sqlRes as $row) {
            $sqlRes2 = $this->db->query('
                select * 
                from ' . DB_PREF . $this->markersTable . '
                where template_id = ?', array($row['id']))->toArray();
            
            $row['markers'] = array();
            foreach ($sqlRes2 as $row2) {
                $row['markers'][] = $row2;
            }
            
            $row['title'] = $this->translator->translateI18n($row['title']);
            
            $row['editLink'] = $this->urlHelper->fromRoute('admin/method', array(
                'module' => 'Templates',
                'method' => 'EditTemplate',
                'id' => $row['id'],
            ));
            
            $row['delLink'] = $this->urlHelper->fromRoute('admin/method', array(
                'module' => 'Templates',
                'method' => 'DeleteTemplate',
            ));
            
            $templates[] = $row;
        }   
        return $templates;
    }
    
    public function getModules()
    {
        $modules = $this->moduleManager->getActiveModules();
        
        foreach ($modules as $k=>$v) {
            $modules[$k]['title'] = $this->translator->translateI18n($modules[$k]['title']);
            if (!empty($v['methods'])) {
                foreach ($v['methods'] as $k2=>$v2) {
                    if (!isset($v2['dynamic_templates']) || !$v2['dynamic_templates']) {
                        unset($modules[$k]['methods'][$k2]);
                    } else {
                         $modules[$k]['methods'][$k2]['title'] = $this->translator->translateI18n( $modules[$k]['methods'][$k2]['title']);
                        $modules[$k]['methods'][$k2]['link'] = $this->urlHelper->fromRoute('admin/TemplatesList', array(
                            'templateModule' => $k,
                            'templateMethod' => $k2,
                        ));
                    }
                }
            }
        }
        
        foreach ($modules as $k=>$v) {
            if (isset($v['dynamic_templates']) && $v['dynamic_templates']) {
                $modules[$k]['link'] = $this->urlHelper->fromRoute('admin/TemplatesList', array(
                    'templateModule' => $k,
                ));
            } else {
                if (empty($v['methods'])) {
                    unset($modules[$k]);
                }
            } 
        }
        
        return $modules;
    }
    
    public function getFormConfig()
    {
        return array(
            'elements' => array(
                'name' => array(
                    'spec' => array(
                        'name' => 'name',
                        'options' => array(
                            'label' => $this->translator->translate('Templates:Template file'),
                        ),
                    ),
                ),
                'title' => array(
                    'spec' => array(
                        'name' => 'title',
                        'options' => array(
                            'label' => $this->translator->translate('Templates:Template name'),
                        ),
                    ),                    
                ),
                'is_default' => array(
                    'spec' => array(
                        'type' => 'checkbox',
                        'name' => 'is_default',
                        'options' => array(
                            'label' => $this->translator->translate('Templates:Is default template'),
                        ),
                    ),
                ),
                'content' => array(
                    'spec' => array(
                        'type' => 'aceEditor',
                        'name' => 'content',
                        'options' => array(
                            'label' => $this->translator->translate('Templates:Template content'),
                            'mode' => 'php',
                        ),
                    ),
                ),
                'markers' => array(
                    'spec' => array(
                        'type' => 'textarea',
                        'name' => 'markers',
                        'options' => array(
                            'label' => $this->translator->translate('Templates:Template markers'),
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'name' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'Regex', 'options' => array('pattern' => '/.+\.phtml$/')),
                    ),
                    'filters' => array(
                        array('name' => 'StringTrim'),  
                        array('name' => 'StringToLower'),
                    ),
                ),
                'title' => array(
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),                        
                    ),
                ),
            ),
        );
    }
        
    public function getPageTemplateDefaultContent()
    {
        return '<div class="container"><?php echo $this->page[\'content\'][\'main_content\'] ?></div>';
    }
    
    public function getPageTemplateDefaultMarkers()
    {
        return 'main_content=Основное содержимое';
    }
    
    public function editTemplate($id, $data)
    {
        $template = $this->getTemplate($id);
        
        $module = $template['module'];
        $method = $template['method'];
        
        if ($data['is_default']) {
            $this->db->query('
                update ' . DB_PREF . $this->templatesTable . '
                set is_default = 0
                where module = ? and method = ?', array($module, $method));
        }
        
        $fileManager = $this->serviceManager->get('fileManager');
        
        $templateDir = $this->coreConfig['view_manager']['template_path_stack'][0] . '/' . $template['type'] . '/' . $module;
        if ($method) {
            $templateDir .= '/' . $method;
        }   
        if (!is_dir($templateDir)) {
            $fileManager->mkdir($templateDir, true);
        }
        
        if ($template['name'] != $data['name']) {
            if (is_file($templateDir . '/' . $template['name'])) {
                unlink($templateDir . '/' . $template['name']);
            }
        }
        
        file_put_contents($templateDir . '/' . $data['name'], $data['content']);
                
        unset($data['content']);
        
        $markersStr = $data['markers'];
        unset($data['markers']);
        
        $sql = new Sql($this->db);
        $update = $sql->update(DB_PREF . $this->templatesTable);
               
        $update->set($data)->where('id = ' . (int)$id);
        
        $sql->prepareStatementForSqlObject($update)->execute();
        
        
        
        $tmp = explode(LF, $markersStr);
        
        $newMarkers = array();
        foreach ($tmp as $v) {
            $parts = explode('=', $v);
            if (2 == count($parts)) {
                $newMarkers[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        $currentMarkers = $template['markers'];
        
        foreach ($currentMarkers as $k=>$v) {
            if (isset($newMarkers[$k])) {
                unset($newMarkers[$k]);
            } else {
                $this->db->query('delete from ' . DB_PREF . $this->markersTable . ' where name = ? and template_id = ?', array($k, $id));
            }
        }
        
        foreach ($newMarkers as $k=>$v) {
            $this->db->query('insert into ' . DB_PREF . $this->markersTable . ' (name, title, template_id) values (?, ?, ?)', array($k, $v, $id));
        }
    }
    
    public function addTemplate($module, $method, $templateType, $data)
    {
        if ($data['is_default']) {
            $this->db->query('
                update ' . DB_PREF . $this->templatesTable . '
                set is_default = 0
                where module = ? and method = ?', array($module, $method));
        }
        
        $sql = new Sql($this->db);
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

        $templateId = $this->db->getDriver()->getLastGeneratedValue();
        
        $fileManager = $this->serviceManager->get('fileManager');
        
        $templateDir = $this->coreConfig['view_manager']['template_path_stack'][0] . '/' . $templateType . '/' . $module;
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
            $sql = new Sql($this->db);
            $insert = $sql->insert(DB_PREF . $this->markersTable);
            
            $insert->values($v);
        
            $sql->prepareStatementForSqlObject($insert)->execute();
        }
    }
    
    public function getTemplate($id)
    {
        $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->templatesTable . ' where id = ? limit 1', array($id))->toArray();
        
        if (empty($sqlRes)) {
            return null;
        }
        
        $result = $sqlRes[0];
        
        $sqlRes = $this->db->query('select name, title from ' . DB_PREF . $this->markersTable . ' where template_id = ?', array($id))->toArray();
        
        $fileManager = $this->serviceManager->get('fileManager');
        
        $templateDir = $this->coreConfig['view_manager']['template_path_stack'][0] . '/' . $result['type'] . '/' . $result['module'];
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
    
    public function deleteTemplate($id)
    {
        $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->templatesTable . ' where id = ? limit 1', array($id))->toArray();
        
        if (empty($sqlRes)) {
            return false;
        }
        $result = $sqlRes[0];
        
        $this->db->query('delete from ' . DB_PREF . $this->templatesTable . ' where id = ?', array($id));
        
        $templateDir = $this->coreConfig['view_manager']['template_path_stack'][0] . '/' . $result['type'] . '/' . $result['module'];
        if ($result['method']) {
            $templateDir .= '/' . $result['method'];
        }   
        
        if (is_file($templateDir . '/' . $result['name'])) {
            unlink($templateDir . '/' . $result['name']);
        }
        
        return true;
    }
}