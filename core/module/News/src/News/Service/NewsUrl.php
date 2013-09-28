<?php

namespace News\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class NewsUrl implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $newsItemPage;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getNewsItemUrl($objectId)
    {
        $urlQuery = $this->getNewsItemUrlQuery($objectId);
        if (null !== $urlQuery) {
            $urlQuery = ROOT_URL_SEGMENT . $urlQuery;
        }
        return $urlQuery;
    }
    
    public function getNewsItemUrlQuery($objectId)
    {
        if (null === $this->newsItemPage) {
            $db = $this->serviceManager->get('db');

            $sqlRes = $db->query('
                select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('News', 'FeNewsItem'))->toArray();

            if (!empty($sqlRes)) {
                $typeId = $sqlRes[0]['id'];

                $sqlRes = $db->query('
                    select page_id from ' . DB_PREF . 'pages_content
                    where page_content_type_id = ?
                        and is_active = 1 
                        and is_deleted = 0', array($typeId))->toArray();

                if (!empty($sqlRes)) {
                    $this->newsItemPage = $sqlRes[0]['page_id'];
                }
            }
        }
        
        
        if (null !== $this->newsItemPage) {
            $pageUrlService = $this->serviceManager->get('Pages\Service\PageUrl');            
            $urlParams = $pageUrlService->getPageUrlParams($this->newsItemPage);
            
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