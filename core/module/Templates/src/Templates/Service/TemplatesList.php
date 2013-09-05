<?php

namespace Templates\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class TemplatesList implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $templatesTable = 'templates';
    
    protected $markersTable = 'template_markers';
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
 
    public function getModuleTemplates($module)
    {
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        $urlHelper = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $sqlRes = $db->query('
            select * 
            from ' . DB_PREF . $this->templatesTable . '
            where module = ?', array($module))->toArray();
        
        $templates = array();
        foreach ($sqlRes as $row) {
            $sqlRes2 = $db->query('
                select * 
                from ' . DB_PREF . $this->markersTable . '
                where template_id = ?', array($row['id']))->toArray();
            
            $row['markers'] = array();
            foreach ($sqlRes2 as $row2) {
                $row['markers'][] = $row2;
            }
            
            $row['showLink'] = $urlHelper->fromRoute('admin/method', array(
                'module' => 'TemplatesList',
                'method' => 'SingleTemplate',
                'id' => $row['id'],
            ));
            
            $row['delLink'] = $urlHelper->fromRoute('admin/method', array(
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
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        $urlHelper = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $sqlRes = $db->query('
            select * 
            from ' . DB_PREF . $this->templatesTable . '
            where module = ? AND method = ?', array($module, $method))->toArray();
        
        $templates = array();
        foreach ($sqlRes as $row) {
            $sqlRes2 = $db->query('
                select * 
                from ' . DB_PREF . $this->markersTable . '
                where template_id = ?', array($row['id']))->toArray();
            
            $row['markers'] = array();
            foreach ($sqlRes2 as $row2) {
                $row['markers'][] = $row2;
            }
            
            $row['title'] = $translator->translateI18n($row['title']);
            
            $row['editLink'] = $urlHelper->fromRoute('admin/method', array(
                'module' => 'Templates',
                'method' => 'EditTemplate',
                'id' => $row['id'],
            ));
            
            $row['delLink'] = $urlHelper->fromRoute('admin/method', array(
                'module' => 'Templates',
                'method' => 'DeleteTemplate',
            ));
            
            $templates[] = $row;
        }   
        return $templates;
    }
    
    public function getModules()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $translator = $this->serviceManager->get('translator');
        $urlHelper = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $k=>$v) {
            $modules[$k]['title'] = $translator->translateI18n($modules[$k]['title']);
            if (!empty($v['methods'])) {
                foreach ($v['methods'] as $k2=>$v2) {
                    if (!isset($v2['dynamic_templates']) || !$v2['dynamic_templates']) {
                        unset($modules[$k]['methods'][$k2]);
                    } else {
                         $modules[$k]['methods'][$k2]['title'] = $translator->translateI18n( $modules[$k]['methods'][$k2]['title']);
                        $modules[$k]['methods'][$k2]['link'] = $urlHelper->fromRoute('admin/TemplatesList', array(
                            'templateModule' => $k,
                            'templateMethod' => $k2,
                        ));
                    }
                }
            }
        }
        
        foreach ($modules as $k=>$v) {
            if (isset($v['dynamic_templates']) && $v['dynamic_templates']) {
                $modules[$k]['link'] = $urlHelper->fromRoute('admin/TemplatesList', array(
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
}