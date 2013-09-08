<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Sql;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;

class UserRegistration implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $usersTable = 'users';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function register($data)
    {       
        $objectTypeId = $this->objectTypeId; 
        $config = $this->serviceManager->get('config');
        $usersConfig = $config['Users'];
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $application = $this->serviceManager->get('application');
        $db = $this->serviceManager->get('db');
        $configManager = $this->serviceManager->get('configManager');
        
        
        $insertFields = array();
        $insertBase = array();

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }
        
        unset($insertBase['passwordVerify']);
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($usersConfig['passwordCost']);
        $password = $bcrypt->create($insertBase['password']);

        $insertBase['password'] = $password;

        $objectId = $objectsCollection->addObject('user-item', $objectTypeId);
        $insertBase['object_id'] = $objectId;

        // If user state is enabled, set the default state value
        if ($usersConfig['enableUserState']) {
            if ($usersConfig['defaultUserState']) {
                $insertBase['state'] = $usersConfig['defaultUserState'];
            }
        }
        
        $application->getEventManager()->trigger('user_registered', $this);

        $sql = new Sql($db);
        $insert = $sql->insert(DB_PREF . $this->usersTable)->values($insertBase);
        $sql->prepareStatementForSqlObject($insert)->execute();    

        $userId = $db->getDriver()->getLastGeneratedValue();

        $objectType = $objectTypesCollection->getType($objectTypeId); 
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) { 
                if ($v2->getIsVisible() && isset($insertFields[$k2])) {
                    $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }
        
        $application->getEventManager()->trigger('user_registered.post', $this, array('userId' => $userId));
        
        
        if ($configManager->get('registration', 'send_welcome_email_to_reg_users')) {
            $subject = $configManager->get('registration', 'welcome_email_subject');
            $text = $configManager->get('registration', 'welcome_email_text');

            $this->sendWelcomeEmail($insertBase, $subject, $text);
        }
                
        return $userId;
    }
    
    
    public function sendWelcomeEmail($data, $subject, $text)
    {        
        foreach ($data as $k=>$v) {
            $v = (string)$v;
            $text = str_replace('{{' . $k . '}}', $v, $text);
        }
        
        $html = new MimePart($text);
        $html->type = "text/html";

        $bodyParts = array();
        $bodyParts[] = $html;
        
        $body = new MimeMessage();
        $body->setParts($bodyParts);        
        
        $recipient = $data['email'];
        
        $configManager = $this->serviceManager->get('configManager');
        $sender = $configManager->get('system', 'admin_email');
        
        $message = new Message();
        $message->addFrom($sender)
                ->addTo($recipient)
                ->setSubject($subject);
        
        $message->setBody($body);
        $message->setEncoding("UTF-8");
        
        $transport = new SendmailTransport();
        $transport->send($message);
    }
}