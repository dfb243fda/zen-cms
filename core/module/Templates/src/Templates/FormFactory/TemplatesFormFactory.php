<?php

namespace Templates\FormFactory;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\InputFilter\Factory as InputFactory;

class TemplatesFormFactory implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $templateId;
    
    protected $templateType;
    
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
    
    public function setTemplateType($templateType)
    {
        $this->templateType = $templateType;
        return $this;
    }
    
    public function getForm()
    {
        if (null === $this->templateId) {
            if ('page_template' == $this->templateType) {
                $withMarkers = true;
                $formData = array(
                    'content' => $this->getDefaultTemplateContent(),
                    'markers' => $this->getDefaultTemplateMarkers(),
                );
            } else {
                $withMarkers = false;
                $formData = array();
            }  
        } else {
            $templateEntity = $this->serviceManager->get('Templates\Entity\TemplateEntity');
            $templateEntity->setTemplateId($this->templateId);
            
            $templateData = $templateEntity->getData();
          
            $formData = $templateData;
            $markersStr = '';
            foreach ($formData['markers'] as $k=>$v) {
                $markersStr .= $k . ' = ' . $v . LF;
            }            
            $formData['markers'] = $markersStr;
            
            if ('page_template' == $templateData['type']) {
                $withMarkers = true;
            } else {
                $withMarkers = false;
            }            
        }
        
        $formElementManager = $this->serviceManager->get('FormElementManager');
        
        if ($withMarkers) {
            $form = $formElementManager->get('Templates\Form\TemplateWithMarkersForm');
            $factory     = new InputFactory();
            $filter = $factory->createInput(array('type' => 'Templates\Form\TemplateWithMarkersFilter'));
            
     //       $filter = $this->serviceManager->get('Templates\Form\TemplateWithMarkersFilter');            
        } else {
            $form = $formElementManager->get('Templates\Form\TemplateForm');
            $factory     = new InputFactory();
            $filter = $factory->createInput(array('type' => 'Templates\Form\TemplateFilter'));
            
    //        $filter = $this->serviceManager->get('Templates\Form\TemplateFilter');
        }
        $form->setInputFilter($filter);
        $form->setData($formData);
        
        return $form;
    }
    
    protected function getDefaultTemplateContent()
    {
        return '<div class="container"><?php echo $this->page[\'content\'][\'main_content\'] ?></div>';
    }
    
    protected function getDefaultTemplateMarkers()
    {
        return 'main_content=Основное содержимое';
    }
    
}