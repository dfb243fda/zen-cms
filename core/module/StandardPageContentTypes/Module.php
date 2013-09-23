<?php

namespace StandardPageContentTypes;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getSearchUrlQuery($sm)
    {
        return array(
            'simple-text-page-content-type' => function($sm, $objectId) {
                $db = $sm->get('db');
                
                $sqlRes = $db->query('
                    select page_id from ' . DB_PREF . 'pages_content
                    where object_id = ?
                ', array($objectId))->toArray();
            
                if (!empty($sqlRes)) {
                    $pageUrlService = $sm->get('Pages\Service\PageUrl');
                    $url = $pageUrlService->getPageUrl($sqlRes[0]['page_id']);
                    if ($url) {
                        return substr($url, strlen(ROOT_URL_SEGMENT));
                    }
                }
                return null;
            },
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $installer = $sm->get('StandardPageContentTypes\Service\Installer');
        $installer->install();
    }
}
