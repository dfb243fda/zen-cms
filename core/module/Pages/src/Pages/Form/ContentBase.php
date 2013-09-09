<?php

namespace Pages\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ContentBase extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function init()
    {    
        $contentTypeId = $this->getOption('contentTypeId');
        
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');
        $moduleManager = $this->serviceLocator->getServiceLocator()->get('moduleManager');
        $db = $this->serviceLocator->getServiceLocator()->get('db');
        $configManager = $this->serviceLocator->getServiceLocator()->get('configManager');
        
        
        $sqlRes = $db->query('select id, title, module from ' . DB_PREF . 'page_content_types', array())->toArray();
                
        $pageContentTypesMultiOptions = array();        
        foreach ($sqlRes as $row) {
            if (!isset($pageContentTypesMultiOptions[$row['module']])) {
                $moduleConfig = $moduleManager->getModuleConfig($row['module']);
                $moduleTitle = $translator->translateI18n($moduleConfig['title']);
                $pageContentTypesMultiOptions[$row['module']] = array(
                    'label' => $moduleTitle,
                );
            }            
            $pageContentTypesMultiOptions[$row['module']]['options'][$row['id']] = $translator->translateI18n($row['title']);
        }
        
        if (null === $contentTypeId) {
            foreach ($pageContentTypesMultiOptions as $k=>$v) {
                foreach ($v['options'] as $k2=>$v2) {
                    $contentTypeId = $k2;
                    break;
                }
                break;
            }
        }
        
        $sqlRes = $db->query('select * from ' . DB_PREF . 'page_content_types where id = ?', array($contentTypeId))->toArray();
        
        $templateMultiOptions = array();
        $templateType = 'content_template';
        $module = null;
        $method = null;
        if (!empty($sqlRes)) {    
            $module = $sqlRes[0]['module'];
            $method = $sqlRes[0]['method'];
            
            $sqlRes = $db->query('
                select id, title
                from ' . DB_PREF . 'templates
                where type = ?
                    and module = ?
                    and method = ?
            ', array($templateType, $module, $method))->toArray();
            
            foreach ($sqlRes as $row) {
                $templateMultiOptions[$row['id']] = $translator->translateI18n($row['title']);
            }
        }
        
        
        $sqlRes = $db->query('
            select id, name
            from ' . DB_PREF . 'roles
            order by sorting
        ', array())->toArray();
                
        $accessMultOptions = array(
            '-2' => 'Всем пользователям',
            '-1' => 'Авторизованным пользователям',
            '0' => 'Неавторизованным пользователям',            
        );
        foreach ($sqlRes as $row) {
            $accessMultOptions[$row['id']] = $row['name'];
        }
        
        
        $sqlRes = $db->query('
            select id, name
            from ' . DB_PREF . 'object_types
            where page_content_type_id = ?
        ', array($contentTypeId));
        
        
        $objectTypesMultiOptions = array();
        foreach ($sqlRes as $row) {
            $objectTypesMultiOptions[$row['id']] = $translator->translateI18n($row['name']);
        }       
                
        
        $this->add(array(
            'name' => 'common',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('Pages:Content common params fields group'),
            ),
        ));
        
        
        $this->get('common')
             ->add(array(
                'name' => 'page_content_type_id',
                'type' => 'select',
                'options' => array(
                    'label' => $translator->translate('Pages:Content type'),
                    'value_options' => $pageContentTypesMultiOptions,
                ),
                'attributes' => array(
                    'id' => 'page_content_type_id',
                ),
            ))
            ->add(array(                
                'name' => 'object_type_id',
                'type' => 'ObjectTypeLink',
                'options' => array(
                    'label' => $translator->translate('Pages:Content data type'),
                    'value_options' => $objectTypesMultiOptions,
                ),
                'attributes' => array(
                    'id' => 'object_type_id',
                ),
            ))
            ->add(array(                
                'name' => 'is_active',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => $translator->translate('Pages:Content is active field'),
                ),
            ))
            ->add(array(
                'name' => 'name',
                'options' => array(
                    'label' => $translator->translate('Pages:Content name'),                                        
                ),
            ));
                
        $this->add(array(
            'name' => 'additional_params',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('Pages:Content additional params fields group'),
            ),
        ));
        
        $this->get('additional_params')
             ->add(array(                
                'name' => 'template',
                'type' => 'templateLink',
                'options' => array(
                    'label' => $translator->translate('Pages:Content template field'),
                    'value_options' => $templateMultiOptions,
                    'module' => $module,
                    'method' => $method,
                ),
                'attributes' => array(
                    'id' => 'template',
                ),
            ))
            ->add(array(
                'name' => 'access',
                'type' => 'Select',                                    
                'options' => array(
                    'label' => $translator->translate('Pages:Content access field'),
                    'value_options' => $accessMultOptions,
                ),
                'attributes' => array(
                    'multiple' => true,
                ),
            ));
               
        foreach ($this->getInputFilter()->getInputs() as $inputFilter) {
            foreach ($inputFilter->getInputs() as $input) {
                $input->setRequired(false);
            }
        }
    
        
        $this->getInputFilter()
             ->get('common')
             ->get('name')
             ->setRequired(true)
             ->getFilterChain()
             ->attachByName('StringTrim');
    }
}