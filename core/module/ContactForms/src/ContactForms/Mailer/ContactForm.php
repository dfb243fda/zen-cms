<?php

namespace ContactForms\Mailer;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;

class ContactForm implements ServiceManagerAwareInterface
{    
    protected $messagesTableName = 'contact_forms_msg';
    
    protected $serviceManager;
    
    /**
     * @var \ContactForms\Entity\ContactForm
     */
    protected $formEntity;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setFormEntity($formEntity)
    {
        $this->formEntity = $formEntity;
        return $this;
    }
    
    public function sendMessages($msgData)
    {        
        $formEntityData = $this->formEntity->getData();
        $zendForm = $this->formEntity->getContactForm();
        $request = $this->serviceManager->get('request');
        $db = $this->serviceManager->get('db');
        
        $configManager = $this->serviceManager->get('configManager');
        $date = new \DateTime();
        $currentDate = $date->format($configManager->get('system', 'date_format'));
        
        $attachmentsDir = APPLICATION_PATH . '/uploads/ContactForms' . DS . $currentDate;
        
        $attachments = array();
        
        $success = true;
                
        $replaceArr = array();
        foreach ($msgData as $k=>$v) {
            if (is_array($v)) {
                if (null === ($file = $request->getFiles($k))) {
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
        
        $recipient = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['recipient']);
        $sender = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['sender']);
        $subject = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['subject']);
        $messageStr = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['mail_template']);
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
        
        $db->query('
            insert into ' . DB_PREF . $this->messagesTableName . '
                (recipient, sender, subject, message, status, created_time, attachments)
            values (?, ?, ?, ?, ?, ?, ?)                
        ', array($recipient, $sender, $subject, $messageStr, $status, $createdTime, $attachmentsStr));
        
        if (!$formEntityData['use_recipient2']) {
            return $success;
        }
        
        $recipient = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['recipient2']);
        $sender = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['sender2']);
        $subject = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['subject2']);
        $messageStr = str_replace(array_keys($replaceArr), array_values($replaceArr), $formEntityData['mail_template2']);
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
        
        $sqlRes = $db->query('
            insert into ' . DB_PREF . $this->messagesTableName . '
                (recipient, sender, subject, message, status, created_time, attachments)
            values (?, ?, ?, ?, ?, ?, ?)                
        ', array($recipient, $sender, $subject, $messageStr, $status, $createdTime, $attachmentsStr));
        
        return $success;
    }
}