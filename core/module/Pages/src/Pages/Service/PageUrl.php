<?php

namespace Pages\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Класс для отрисовки дерева страниц в админке
 */
class PageUrl implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    
    public function getPageUrlParams($pageId)
    {
        $db = $this->serviceManager->get('db');
        $configManager = $this->serviceManager->get('configManager');
        
        $sqlRes = $db->query('select t1.alias, (select t2.name from ' . DB_PREF . 'objects t2 where t2.id = t1.object_id) as title from ' . DB_PREF . 'pages t1 where t1.id = ?', array($pageId))->toArray();
        
        if (empty($sqlRes)) {
            return null;
        }
        
        if ($sqlRes[0]['alias'] == '') {
            $pageAlias = $sqlRes[0]['title'];
        } else {
            $pageAlias = $sqlRes[0]['alias'];
        }
        $replaceSpacesWith = $configManager->get('pages', 'replace_spaces_with');
        $pageAlias = str_replace(' ', $replaceSpacesWith, $pageAlias);
                
        return array(
            'pageId' => $pageId,
            'pageAlias' => $pageAlias,
        );
    }
    
    public function getPageUrl($pageId)
    {
        $urlParams = $this->getPageUrlParams($pageId);
        
        if (null === $urlParams) {
            return null;
        }        
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $url = $urlPlugin->fromRoute('fe/page', $urlParams);
        
        return $url;
    }
}