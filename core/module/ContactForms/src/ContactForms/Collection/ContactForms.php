<?php

namespace ContactForms\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class ContactForms implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $tableName = 'contact_forms';
        
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    /**
     * return \ContactForms\Entity\Form
     */
    public function getFormEntity($formId)
    {
        $formEntity = $this->serviceManager->get('ContactForms\Entity\ContactForm');
        $formEntity->setFormId($formId)->init();
        return $formEntity;
    }
    
    /**
     * return \ContactForms\Entity\Form|null
     */
    public function getFormEntityByObjectId($objectId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select id from ' . DB_PREF . $this->tableName . ' where object_id = ? limit 1', array($objectId))->toArray();
        
        if (empty($sqlRes)) {
            return null;
        }
        return $this->getFormEntity($sqlRes[0]['id']);
    }
    
    /**
     * @return \Zend\Form\Form
     */
    public function getAdminForm()
    {
        $form = $this->serviceManager->get('ContactForms\Form\ContactForm');        
        $form->init();        
        $form->setData($this->getDefaultData());
        
        return $form;
    }
    
    public function delContactForm($formId)
    {
        $db = $this->serviceManager->get('db');
        $db->query('
                delete from ' . DB_PREF . $this->tableName . '
                where id = ?
            ', array($formId));
        
        return true;
    }
    
    public function addContactForm($values)
    {
        $db = $this->serviceManager->get('db');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        $cfObjectTypeId = $objectTypesCollection->getTypeIdByGuid('contact-form');
        
        if (null === $cfObjectTypeId) {
            return null;
        }
        
        $objectId = $objectsCollection->addObject($values['name'], $cfObjectTypeId);
        
        $values['object_id'] = $objectId;
        
        $sql = new Sql($db);
        
        $insert = $sql->insert()->into(DB_PREF . $this->tableName)->values($values);
        
        $sql->prepareStatementForSqlObject($insert)->execute();  
        
        return $db->getDriver()->getLastGeneratedValue();
    }
    
    protected function getDefaultData()
    {
        $configManager = $this->serviceManager->get('configManager');
        
        return array(
            'template' => $this->getDefaultFormTemplate(),
            
            'recipient' => $configManager->get('system', 'admin_email'),
            'sender' => '[your-name] <[your-email]>',
            'subject' => '[your-subject]',
            'mail_template' => $this->getDefaultMailTemplate(),
            
            'recipient2' => '[your-email]',
            'sender2' => '[your-name] <[your-email]>',
            'subject2' => '[your-subject]',
            'mail_template2' => $this->getDefaultMailTemplate2(),
        );
    }
    
    protected function getDefaultFormTemplate()
    {
        return trim('
<p>Ваше имя (обязательно)<br />
    [text* your-name] </p>
<p>Ваш E-Mail (обязательно)<br />
    [text* your-email] </p>

<p>Тема<br />
    [text your-subject] </p>
    
<p>Сообщение<br />
    [textarea your-message] </p>

<p>[submit "Отправить"]</p>
            ');
    }
    
    protected function getDefaultMailTemplate()
    {
        $configManager = $this->serviceManager->get('configManager');
        $siteName = $configManager->get('system', 'site_name');
        
        return trim('
От: [your-name] <[your-email]>
Тема: [your-subject]

Сообщение:
[your-message]

--
Это сообщение отправлено с сайта ' . $siteName . ' ' . ROOT_URL . '

            ');
    }
    
    protected function getDefaultMailTemplate2()
    {
        $configManager = $this->serviceManager->get('configManager');
        $siteName = $configManager->get('system', 'site_name');
        
        return trim('
Сообщение:
[your-message]

--
Это сообщение отправлено с сайта ' . $siteName . ' ' . ROOT_URL . '

            ');
    }   
}