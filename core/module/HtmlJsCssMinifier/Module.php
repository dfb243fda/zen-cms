<?php

namespace HtmlJsCssMinifier;

/**
 * Module class required for module to be initialized in ZF2 application
 */
class Module
{
    /**
     * Retrieve autoloader configuration for the module
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
            ),
        ));
    }  

    /**
     * Retrieve application configuration for this module
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $configManager = $sm->get('configManager');
        
        if (!$configManager->has('HtmlJsCssMinifier', 'minifyHtml')) {
            $configManager->set('HtmlJsCssMinifier', 'minifyHtml', true);
        }
        if (!$configManager->has('HtmlJsCssMinifier', 'minifyJs')) {
            $configManager->set('HtmlJsCssMinifier', 'minifyJs', true);
        }
        if (!$configManager->has('HtmlJsCssMinifier', 'minifyCss')) {
            $configManager->set('HtmlJsCssMinifier', 'minifyCss', true);
        }        
    }
    
    public function onBootstrap($e)
    {
        $app = $e->getTarget();
        
        $locator = $app->getServiceManager();
        $eventManager = $app->getEventManager();
        
        $htmlJsCssOptimizerService = $locator->get('HtmlJsCssMinifier\Service\HtmlJsCssMinifier');
        $configManager = $locator->get('configManager');
                
        $eventManager->attach('prepare_public_resources', function($e) use ($htmlJsCssOptimizerService, $configManager) {  
            $htmlJsCssOptimizerService->prepareHeadLink($configManager->get('HtmlJsCssMinifier', 'minifyCss'));
            $htmlJsCssOptimizerService->prepareHeadScript($configManager->get('HtmlJsCssMinifier', 'minifyJs'));
            $htmlJsCssOptimizerService->prepareInlineScript($configManager->get('HtmlJsCssMinifier', 'minifyJs'));
        });
        
        $eventManager->attach('prepare_output.post', function($e) use ($htmlJsCssOptimizerService, $configManager, $locator) {               
            $params = $e->getParams();   
            
            if ('html' == $params['format']) {      
                if ($configManager->get('HtmlJsCssMinifier', 'minifyHtml')) {
                    $options = array(
                        'minifyCss' => true,
                        'minifyJs' => true,
                        'jsCleanComments' => true,
                    );

                    $params['result'] = $htmlJsCssOptimizerService->minifyHtml($params['result'], $options);  
                }                              
            }
            
  /*          if (!@ini_get('zlib.output_compression') && (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) ) {
                $filter = new \Zend\Filter\Compress('Gz');
                $response = $locator->get('response');
        
                $response->getHeaders()->addHeaderLine('Content-Encoding', 'gzip');
                $params['result'] = $filter->filter($params['result']);
            }
   * 
   */
      
        });
    }
    
    

}
