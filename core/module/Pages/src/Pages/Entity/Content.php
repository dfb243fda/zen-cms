<?php

namespace Pages\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Content implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $pageId;
    
    protected $templateId;
    
    protected $pagesContentTable = 'pages_content';
    
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
    
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
        return $this;
    }
    
    public function getMarkers()
    {
        $pageId = $this->pageId;
        $templateId = $this->templateId;
        
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $sqlRes = $db->query('
            select id, marker, page_content_type_id, is_active, object_id
            from ' . DB_PREF . $this->pagesContentTable . ' 
            where page_id = ? and is_deleted = 0 
            order by sorting', array($pageId))->toArray();
        
        $contentModules = array();
        foreach ($sqlRes as $row) {
            
            $sqlRes2 = $db->query('
                select title 
                from ' . DB_PREF . 'page_content_types 
                where id = ?', array($row['page_content_type_id']))->toArray();
            
            if (empty($sqlRes2)) {
                $moduleTitle = str_replace('###1###', $row['page_content_type_id'], $translator->translate('Undefined method "###1###"'));
            } else {
                $moduleTitle = $translator->translateI18n($sqlRes2[0]['title']);
            }
            
            $sqlRes2 = $db->query('
                select name from ' . DB_PREF . 'objects
                where id = ?', array($row['object_id']))->toArray();
            
            if (empty($sqlRes2)) {
                $contentTitle = 'undefined method [' . $row['object_id'] . ']';
            } else {
                $contentTitle = $sqlRes2[0]['name'];
            }
            
            $contentModules[$row['marker']][] = array(
                'id' => $row['id'],
                'moduleTitle' => $moduleTitle,
                'contentTitle' => $contentTitle,
                'is_active' => $row['is_active'],
            );
        }
        
        $sqlRes = $db->query('
            select * from ' . DB_PREF . $this->markersTable . '
            where template_id = ?', array($templateId));
                     
        $markers = array();
        foreach ($sqlRes as $row) {            
            if (isset($contentModules[$row['id']])) {
                $row['modules'] = $contentModules[$row['id']];
                unset($contentModules[$row['id']]);
            } else {
                $row['modules'] = array();
            }
            
            $markers[] = $row;
        }
        
        if (!empty($contentModules)) {
            $tmp = array();
            foreach ($contentModules as $row) {
                $tmp = array_merge($tmp, $row);
            }
            
            $markers[] = array(
                'title' => $translator->translate('Markers from another templates'),
                'name' => '',
                'modules' => $tmp, 
            );
        }
        
        return $markers;
    }
}