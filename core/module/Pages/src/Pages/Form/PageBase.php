<?php

namespace Pages\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class PageBase extends Form implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $pageTypeId;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setPageTypeId($typeId)
    {
        $this->pageTypeId = $typeId;
        return $this;
    }
    
    public function create()
    {    
        $pageTypeId = $this->pageTypeId;
        
        $translator = $this->serviceManager->get('translator');
        $moduleManager = $this->serviceManager->get('moduleManager');
        $db = $this->serviceManager->get('db');
        $configManager = $this->serviceManager->get('configManager');
        
        $feTheme = $configManager->get('system', 'fe_theme');
                
        $templateType = 'page_template';
        
        $sqlRes = $db->query('select id, title from ' . DB_PREF . 'templates where type = ? and module = ?', array($templateType, $feTheme))->toArray();
        
        $templateMultiOptions = array();
        foreach ($sqlRes as $row) {
            $templateMultiOptions[$row['id']] = $translator->translateI18n($row['title']);
        }
        
        $sqlRes = $db->query('select id, name from ' . DB_PREF . 'roles order by sorting', array())->toArray();
                
        $accessMultOptions = array(
            '-2' => 'Всем пользователям',
            '-1' => 'Авторизованным пользователям',
            '0' => 'Неавторизованным пользователям',            
        );
        foreach ($sqlRes as $row) {
            $accessMultOptions[$row['id']] = $row['name'];
        }
        
        $sqlRes = $db->query('select id, title from ' . DB_PREF . 'langs', array())->toArray();
                
        $langMultiOptions = array();
        foreach ($sqlRes as $row) {
            $langMultiOptions[$row['id']] = $row['title'];
        }
        
        $sqlRes = $db->query('select id, title, module from ' . DB_PREF . 'page_types', array())->toArray();
        
        $pageTypesMultiOptions = array();
        foreach ($sqlRes as $row) {
            if (!isset($pageTypesMultiOptions[$row['module']])) {
                $moduleConfig = $moduleManager->getModuleConfig($row['module']);
                $moduleTitle = $translator->translateI18n($moduleConfig['title']);
                $pageTypesMultiOptions[$row['module']] = array(
                    'label' => $moduleTitle,
                );
            }                        
            $pageTypesMultiOptions[$row['module']]['options'][$row['id']] = $translator->translateI18n($row['title']);
        }
        
        if (null === $pageTypeId) {
            foreach ($pageTypesMultiOptions as $k=>$v) {
                foreach ($v['options'] as $k2=>$v2) {
                    $pageTypeId = $k2;
                    break;
                }
                break;
            }
        }
        
        
        $sqlRes = $db->query('select id, name from ' . DB_PREF . 'object_types where page_type_id = ?', array($pageTypeId))->toArray();
        
        $objectTypesMultiOptions = array();
        foreach ($sqlRes as $row) {
            $objectTypesMultiOptions[$row['id']] = $translator->translateI18n($row['name']);
        }        
                
        $this->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
        
        $this->add(array(
            'name' => 'common',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('Pages:Common params fields group'),
            ),
        ));
        
        $this->get('common')
             ->add(array(
                'name' => 'page_type_id',
                'type' => 'select',
                'options' => array(
                    'label' => $translator->translate('Pages:Page type'),
                    'value_options' => $pageTypesMultiOptions,
                ),
                'attributes' => array(
                    'id' => 'page_type_id',
                ),
            ))
            ->add(array(                
                'name' => 'object_type_id',
                'type' => 'ObjectTypeLink',
                'options' => array(
                    'label' => $translator->translate('Pages:Data type'),
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
                    'label' => $translator->translate('Pages:Activity'),
                ),
            ))
            ->add(array(
                'name' => 'name',
                'options' => array(
                    'label' => $translator->translate('Pages:Page name'),                                        
                ),
            ))
            ->add(array(
                'name' => 'alias',
                'options' => array(
                    'label' => $translator->translate('Pages:Page alias (For URL)'),
                ),
            ));
        
        $this->add(array(
            'name' => 'additional_params',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('Pages:Additional params fields group'),
            ),
        ));
        
        $this->get('additional_params')
             ->add(array(                
                'name' => 'template',
                 'type' => 'templateLink',
                'options' => array(
                   'label' => $translator->translate('Pages:Template'),
                   'value_options' => $templateMultiOptions,
                   'module' => $feTheme,
                ),
                'attributes' => array(
                   'id' => 'template',
                ),
            ))
            ->add(array(                
                'name' => 'is_default',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => $translator->translate('Pages:Is default field'),
                ),
            ))
            ->add(array(
                'name' => 'access',
                'type' => 'Select',                                    
                'options' => array(
                    'label' => $translator->translate('Pages:Access field'),
                    'value_options' => $accessMultOptions,
                ),
                'attributes' => array(
                    'multiple' => true,
                ),
            ))
            ->add(array(
                'name' => 'non_access_url',
                'options' => array(
                    'label' => $translator->translate('Pages:Non access url'),
                ),
            ))
            ->add(array(
                'name' => 'lang_id',
                'type' => 'Select',                                    
                'options' => array(
                    'label' => $translator->translate('Pages:Language field'),
                    'value_options' => $langMultiOptions,
                ),
            ));
        
        $this->add(array(
            'name' => '403_404',
            'type' => 'fieldset',
            'options' => array(
                'label' => $translator->translate('Pages:403 404 fields group'),
            ),
        ));
        
        $this->get('403_404')
             ->add(array(
                'type' => 'checkbox',
                'name' => 'is_403',
                'options' => array(
                   'label' => $translator->translate('Pages:Is 403 page field'),
                   'description' => $translator->translate('Pages:Is 403 page field description'),
                ),
             ))
            ->add(array(
                'type' => 'checkbox',
                'name' => 'is_404',                
                'options' => array(
                    'label' => $translator->translate('Pages:Is 404 page field'),
                    'description' => $translator->translate('Pages:Is 404 page field description'),
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
             ->attachByName('Zend\Filter\StringTrim');
    }
}