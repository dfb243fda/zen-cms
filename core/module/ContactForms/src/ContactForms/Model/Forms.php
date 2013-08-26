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