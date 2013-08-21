<?php

namespace ContactForms\Model;

use Zend\Db\Sql\Sql;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;

class Forms
{
    protected $translator;
    
    protected $tableName = 'contact_forms';
    
    protected $messagesTableName = 'contact_forms_msg';
    
    protected $serviceManager;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        
        $this->translator = $sm->get('translator');
        
        $this->tableName = DB_PREF . $this->tableName;
        $this->messagesTableName = DB_PREF . $this->messagesTableName;
        $this->request = $sm->get('request');
        
        $this->db = $sm->get('db');
    }
    
    public function getFormConfig()
    {
        return array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'name',
                        'options' => array(
                            'label' => $this->translator->translate('Contact form name'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'template',
                        'type' => 'aceEditor',
                        'options' => array(
                            'label' => $this->translator->translate('Contact form template'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'recipient',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Recipient field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'sender',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Sender field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'subject',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Subject field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'mail_template',
                        'type' => 'aceEditor',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Mail template field'),
                        ),
                    ),
                ),
                
                array(
                    'spec' => array(
                        'name' => 'use_recipient2',
                        'type' => 'checkbox',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Use recipient-2'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'recipient2',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Recipient field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'sender2',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Sender field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'subject2',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Subject field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'mail_template2',
                        'type' => 'aceEditor',
                        'options' => array(
                            'label' => $this->translator->translate('ContactForms:Mail template field'),
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'name' => array(
                    'required' => true,
                ),
            ),
        );
    }
    
    public function getDefaultFormValues()
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
    
    public function addContactForm($values)
    {
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        $cfObjectTypeId = $objectTypesCollection->getTypeIdByGuid('contact-form');
        
        if (null === $cfObjectTypeId) {
            return null;
        }
        
        $objectId = $objectsCollection->addObject($values['name'], $cfObjectTypeId);
        
        $values['object_id'] = $objectId;
        
        $sql = new Sql($this->db);
        
        $insert = $sql->insert()->into($this->tableName)->values($values);
        
        $sql->prepareStatementForSqlObject($insert)->execute();  
        
        return $this->db->getDriver()->getLastGeneratedValue();
    }
    
    public function getFormValues($formId)
    {
        $sqlRes = $this->db->query('select * from ' . $this->tableName . ' where id = ?', array($formId))->toArray();
        
        if (empty($sqlRes)) {
            return null;
        }
        return $sqlRes[0];
    }
    
    public function editContactForm($formId, $values)
    {
        $sql = new Sql($this->db);
        
        $update = $sql->update()->table($this->tableName)->set($values)->where('id = ' . (int)$formId);
        
        $sql->prepareStatementForSqlObject($update)->execute();  
    }
    
    public function delContactForm($formId)
    {
        $this->db->query('
                delete from ' . $this->tableName . '
                where id = ?
            ', array($formId));
        
        return true;
    }
    
    public function sendMessages($formId, $data, $zendForm)
    {
        $sqlRes = $this->db->query('select * from ' . $this->tableName . ' where object_id = ?', array($formId))->toArray();
        
        if (empty($sqlRes)) {
            return false;
        }
        
        $configManager = $this->serviceManager->get('configManager');
        $date = new \DateTime();
        $currentDate = $date->format($configManager->get('system', 'date_format'));
        
        $attachmentsDir = APPLICATION_PATH . '/uploads/ContactForms' . DS . $currentDate;
        
        $attachments = array();
        
        $success = true;
                
        $replaceArr = array();
        foreach ($data as $k=>$v) {
            if (is_array($v)) {
                if (null === ($file = $this->request->getFiles($k))) {
                    $v = implode(', ', $v);
                } else {
                    if (isset($v['error']) && $v['error'] == UPLOAD_ERR_OK) {                        
                        if (!is_dir($attachmentsDir)) {
                            $fileManager = $this->serviceManager->get('fileManager');
                            $fileManager->mkdir($attachmentsDir, true);
                        }

                        $filter = new \Zend\Filter\File\RenameUpload(array(
                            'target' => $attachmentsDir,
                            'use_upload_extension' => true,
                            'randomize' => true,
                        ));
                        $fileName = $filter->filter($file);

                        $filePath = substr($fileName['tmp_name'], strlen(APPLICATION_PATH . '/uploads/ContactForms'));

                        $attachments[$k] = array(
                            'filePath' => $filePath,
                            'mimeType' => $fileName['type'],
                        );

                        $v = $v['name'];
                    } else {
                        $v = '';
                    }                    
                }
                
            } elseif ($v instanceof \DateTime) {                  
      //          $v = $v->getTimestamp();
                $format = $zendForm->get($k)->getFormat();
                $v = $v->format($format);
            }
            $replaceArr['[' . $k . ']'] = $v;
        }
        
        $now = time();
        
        $recipient = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['recipient']);
        $sender = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['sender']);
        $subject = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['subject']);
        $messageStr = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['mail_template']);
        $status = 0;
        $createdTime = $now;
        
        $message = new Message();
        $message->addFrom($sender)
                ->addTo($recipient)
                ->setSubject($subject);
        
        
        $html = new MimePart($messageStr);
        $html->type = "text/html";

        $bodyParts = array();
        $bodyParts[] = $html;
        
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $filePath = APPLICATION_PATH . '/uploads/ContactForms' . $attachment['filePath'];
                $image = new MimePart(fopen($filePath, 'r'));
                $image->type = $attachment['mimeType'];
                $bodyParts[] = $image;
            }            
        }
        

        $body = new MimeMessage();
        $body->setParts($bodyParts);
        
        
        $message->setBody($body);
        $message->setEncoding("UTF-8");
        
        $transport = new SendmailTransport();
        $transport->send($message);

        
        
        $tmp = array();
        foreach ($attachments as $attachment) {
            $tmp[] = $attachment['filePath'];
        }
        $attachmentsStr = implode(',', $tmp);
        
        $this->db->query('
            insert into ' . $this->messagesTableName . '
                (recipient, sender, subject, message, status, created_time, attachments)
            values (?, ?, ?, ?, ?, ?, ?)                
        ', array($recipient, $sender, $subject, $messageStr, $status, $createdTime, $attachmentsStr));
        
        if (!$sqlRes[0]['use_recipient2']) {
            return $success;
        }
        
        $recipient = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['recipient2']);
        $sender = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['sender2']);
        $subject = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['subject2']);
        $messageStr = str_replace(array_keys($replaceArr), array_values($replaceArr), $sqlRes[0]['mail_template2']);
        $status = 0;
        $createdTime = $now;
        
        $message = new Message();
        $message->addFrom($sender)
                ->addTo($recipient)
                ->setSubject($subject);
        
        
        $html = new MimePart($messageStr);
        $html->type = "text/html";

        $bodyParts = array();
        $bodyParts[] = $html;
        
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $filePath = APPLICATION_PATH . '/uploads/ContactForms' . $attachment['filePath'];
                $image = new MimePart(fopen($filePath, 'r'));
                $image->type = $attachment['mimeType'];
                $bodyParts[] = $image;
            }            
        }
        

        $body = new MimeMessage();
        $body->setParts($bodyParts);        
        
        $message->setBody($body);
        $message->setEncoding("UTF-8");
        
        $transport = new SendmailTransport();
        $transport->send($message);
        
        $sqlRes = $this->db->query('
            insert into ' . $this->messagesTableName . '
                (recipient, sender, subject, message, status, created_time, attachments)
            values (?, ?, ?, ?, ?, ?, ?)                
        ', array($recipient, $sender, $subject, $messageStr, $status, $createdTime, $attachmentsStr));
        
        return $success;
    }
}