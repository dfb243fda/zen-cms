<?php

namespace ContactForms\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

/**
 * Класс для отрисовки дерева страниц в админке
 */
class ContactForm implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $formId;
    
    protected $tableName = 'contact_forms';
    
    protected $data;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function init()
    {
        if ($this->data) {
            return;
        }
        
        $db = $this->serviceManager->get('db');
        $sqlRes = $db->query('select * from ' . DB_PREF . $this->tableName . ' where id = ?', array($this->formId))->toArray();
        if (empty($sqlRes)) {
            throw new \Exception('Form ' . $this->formId . ' does not found');
        }
        $this->data = $sqlRes[0];
    }
    
    public function setFormId($formId)
    {
        $this->formId = $formId;
        return $this;
    }
    
    public function getFormId()
    {
        return $this->formId;
    }
    
    public function getAdminForm()
    {        
        $form = $this->serviceManager->get('ContactForms\Form\ContactForm');        
        $form->init();
        
        $form->setData($sqlRes[0]);
        
        return $form;
    }
    
    public function editContactForm($data)
    {
        $sql = new Sql($this->serviceManager->get('db'));
        
        $update = $sql->update()->table(DB_PREF . $this->tableName)->set($data)->where('id = ' . (int)$this->formId);
        
        $sql->prepareStatementForSqlObject($update)->execute();  
    }
    
    public function getContactForm()
    {
        exit('dd');
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getContactFormHtml()
    {
        $formParser = $this->serviceManager->get('ContactForms\Parser\ContactForm');
        
        return $formParser->setFormEntity($this)->getHtml();
    }
}