<?php

namespace Templates\FormFactory;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class TemplatesFormFactory implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $templateId;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
        return $this;
    }
    
    public function getForm()
    {
        if (null === $this->templateId) {
            
        } else {
            $templateEntity = $this->serviceManager->get('Templates\Entity\TemplateEntity');
            $templateEntity->setTemplateId($this->templateId);
            
            $templateData = $templateEntity->getData();
            
            if ($templateData['method']) {
                $withMarkers = true;
            } else {
                $withMarkers = false;
            }
            $formData = array();
        }
        
        if ($withMarkers) {
            $form = $this->serviceManager->get('Templates\Form\TemplateWithMarkersForm');
        } else {
            $form = $this->serviceManager->get('Templates\Form\TemplateForm');
        }
        $form->init();
        
        $form->setData($formData);
        
        return $form;
    }
    
}