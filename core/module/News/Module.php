<?php

namespace News;

class Module
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

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getSearchUrlQuery($sm)
    {
        return array(
            'news' => function($sm, $objectId) {
                $newsService = $sm->get('News\Service\NewsUrl');
                return $newsService->getNewsItemUrlQuery($objectId);
            },
        );
    }
    
    public function onInstall($sm)
    {        
        if (!$sm->has('News\Service\Installer')) {
            require_once __DIR__ . '/src/News/Service/Installer.php';
            $sm->setInvokableClass('News\Service\Installer', 'News\Service\Installer');
        }
        
        $installerService = $sm->get('News\Service\Installer');
        $installerService->install();
    }
}