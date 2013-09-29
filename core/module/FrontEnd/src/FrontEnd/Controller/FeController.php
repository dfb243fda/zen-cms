<?php

namespace FrontEnd\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ResponseInterface as Response;

class FeController extends AbstractActionController
{
    /**
     * @var \Pages\Entity\Page
     */
    protected $page;
    
    public function indexAction()
    {
        $page = $this->page = $this->serviceLocator->get('Pages\Entity\Page');
        $userDataService = $this->serviceLocator->get('Users\Service\UserData');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('FrontEnd\View\RendererStrategyOptions');    
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        $configManager = $this->serviceLocator->get('configManager');
        $translator = $this->serviceLocator->get('translator');
        
        define('CURRENT_THEME', $configManager->get('system', 'fe_theme'));
        
        if (isset($this->config[CURRENT_THEME]['exceptionTemplate'])) {
            $this->viewManager->getExceptionStrategy()->setExceptionTemplate($this->config[CURRENT_THEME]['exceptionTemplate']);
        }               
        if (isset($this->config[CURRENT_THEME]['notFoundTemplate'])) {
            $this->viewManager->getRouteNotFoundStrategy()->setNotFoundTemplate($this->config[CURRENT_THEME]['notFoundTemplate']);
        }
        if (is_dir(APPLICATION_PATH . '/view/theme_override/' . CURRENT_THEME)) {
            $this->serviceLocator->get('ViewTemplatePathStack')->addPath(APPLICATION_PATH . '/view/theme_override/' . CURRENT_THEME);
        }        
        
        $resultArray = array();
        
        $resultArray['root_url'] = ROOT_URL;
        
        $resultArray['user'] = $userDataService->getUserData();
        
        $resultArray['page'] = $page->getPageData();
        
        if (isset($resultArray['page']['page_type_id'])) {
            $pageType = $this->serviceLocator->get('Pages\Entity\PageType');            
            $pageType->setPageTypeId($resultArray['page']['page_type_id'])->prepareResult($resultArray);
        }    
        
        $resultArray['systemInfo'] = $systemInfoService->getSystemInfo();
        
        $errors = $errorsService->getErrors();
        if (!empty($errors)) {
            $resultArray['errors'] = $errors;
        }
        
        if (isset($resultArray['page']['language'])) {
            $translator->setLocale($resultArray['page']['language']);
        }
        
        if (isset($resultArray['page']['content']) && ($resultArray['page']['content'] instanceof Response)) {
            return $resultArray['page']['content'];
        }
        if (isset($resultArray['page']['redirectUrl'])) {
            return $this->redirect()->toUrl($resultArray['page']['redirectUrl']);
        }
        if ($this->response->isRedirect()) {
            return $this->response;
        }       
        
        $this->response->setStatusCode($resultArray['page']['statusCode']);
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
        
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
    }
    
    public function getPageEntity()
    {
        return $this->page;
    }
}
