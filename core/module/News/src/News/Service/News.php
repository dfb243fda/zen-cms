<?php

namespace News\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class News implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $singleNewsPage;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getSingleNewsUrl($objectId)
    {
        $urlQuery = $this->getSingleNewsUrlQuery($objectId);
        if (null !== $urlQuery) {
            $urlQuery = ROOT_URL_SEGMENT . $urlQuery;
        }
        return $urlQuery;
    }
    
    public function getSingleNewsUrlQuery($objectId)
    {        
        if (null !== $this->singleNewsPage) {
            $pageService = $this->serviceManager->get('Pages\Service\Page');         
            $urlParams = $pageService->getPageUrlParams($this->singleNewsPage);
            if (is_array($urlParams)) {
                $urlParams['itemId'] = $objectId;
                
                $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
                $url = $urlPlugin->fromRoute('fe/page', $urlParams);
                
                $url = substr($url, strlen(ROOT_URL_SEGMENT));
                
                return $url;
            }
            return null;
        }
        
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
            where module = ? and method = ?', array('News', 'FeNewsItem'))->toArray();
        
        if (!empty($sqlRes)) {
            $typeId = $sqlRes[0]['id'];
            
            $sqlRes = $db->query('
                select page_id from ' . DB_PREF . 'pages_content
                where page_content_type_id = ?', array($typeId))->toArray();
            
            if (!empty($sqlRes)) {
                $this->singleNewsPage = $sqlRes[0]['page_id'];
            }
        }
        
        if (null !== $this->singleNewsPage) {
            $pageService = $this->serviceManager->get('Pages\Service\Page');            
            $urlParams = $pageService->getPageUrlParams($this->singleNewsPage);
            
            if (is_array($urlParams)) {
                $urlParams['itemId'] = $objectId;
                
                $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
                $url = $urlPlugin->fromRoute('fe/page', $urlParams);
                
                $url = substr($url, strlen(ROOT_URL_SEGMENT));
                
                return $url;
            }
        }
        
        return null;
    }
}