<?php

namespace Comments\View\Helper;

use Comments\Service\Comments;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\Model\ViewModel;

class CommentsList extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $commentsService;
    
    public function __construct(Comments $commentsService)
    {
        $this->commentsService = $commentsService;
    }
        
    public function __invoke($objectId, $template = null)
    {     
        $rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $config = $rootServiceLocator->get('config');
        
        $commentsConfig = $config['Comments'];
     
        if (null === $template) {
            $template = $commentsConfig['defaultTemplate'];
        }
        
        $viewModel = new ViewModel(); 
        
        $comments = $this->commentsService->getComments($objectId);
                
        $isAllowedComments = $this->commentsService->isAllowedComments($objectId);
        
        $usersService = $rootServiceLocator->get('users_auth_service');
        
        $user = $usersService->getIdentity();
                        
        $isAllowedCommentsToUser = $this->commentsService->isAllowedCommentsToUser($objectId, $user);
        
        $viewModel->setVariables(array(
            'comments' => $comments,
            'isAllowedComments' => $isAllowedComments,
            'isAllowedCommentsToUser' => $isAllowedCommentsToUser,
        ));
        
        $viewModel->setTemplate($template);
        
        return $this->getView()->render($viewModel);
    }
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}