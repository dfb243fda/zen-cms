<?php

namespace Users\Model;

use Zend\Form\Factory;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Sql;
use Zend\Http\PhpEnvironment\Response;

class Users
{
    protected $serviceManager;
    
    protected $table = 'users';
    
    protected $userRoleLinkerTable = 'user_role_linker';
    
    protected $objectTypeId;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->db = $sm->get('db');
        $this->translator = $sm->get('translator');
        $this->table = DB_PREF . $this->table;
        $this->userRoleLinkerTable = DB_PREF . $this->userRoleLinkerTable;
        $this->objectPropertyCollection = $sm->get('objectPropertyCollection');
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        $this->objectsCollection = $sm->get('objectsCollection');
        $this->request = $sm->get('request');
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function getItems()
    {
        $items = array();    
        
        $sqlRes = $this->db->query('select user_id, display_name, username, email from ' . $this->table, array())->toArray();
        
        foreach ($sqlRes as $row) {
            $row['state'] = 'open';
            
            if ($row['display_name'] != '') {
                $row['name'] = $row['display_name'];
            } elseif ($row['username'] != '') {
                $row['name'] = $row['username'];
            } else {
                $row['name'] = $row['email'];
            }
            $row['id'] = $row['user_id'];
            
            $items[] = $row;
        }
        
        return $items;
    }
    
    public function getForm($userId = null, $onlyVisible = false)
    {             
        $objectTypeId = $this->objectTypeId;      
        
        if ($userId === null) {
            $formConfig = $this->getBaseFormConfig();  
            
            if (null !== $objectTypeId) {
                $objectType = $this->objectTypesCollection->getType($objectTypeId);            
                $formConfig = $objectType->getAppFormConfig($formConfig, $onlyVisible);
                unset($formConfig['fieldsets']['common']['spec']['elements']['name']);
            }
            
            $formValues = array();
            
            $userData = array();
            $userData['object_type_id'] = $objectTypeId;
            $userData['roles'] = array();
            
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if (isset($userData[$k2])) {
                        $formValues[$k][$k2] = $userData[$k2];
                    }
                }
            }
        } else {
            $sqlRes = $this->db->query('select username, email, display_name, object_id from ' . $this->table . ' where user_id = ?', array($userId))->toArray();
        
            if (empty($sqlRes)) {
                throw new \Exception('not found user ' . $userId);
            }
            
            $objectId = $sqlRes[0]['object_id'];
            $object = $this->objectsCollection->getObject($objectId);
            
            $formValues = array();
            
            $userData = $sqlRes[0];
            
            $userData['roles'] = array();
                        
            $sqlRes = $this->db->query('select role_id from ' . DB_PREF . 'user_role_linker where user_id = ?', array($userId))->toArray();
            foreach ($sqlRes as $row) {
                $userData['roles'][] = $row['role_id'];
            }
            
            if (null === $objectTypeId) {
                $objectTypeId = $object->getTypeId();
            }
            
            $formConfig = $this->getBaseFormConfig();              
            
            $objectType = $this->objectTypesCollection->getType($objectTypeId);            
            $formConfig = $objectType->getAppFormConfig($formConfig, $onlyVisible);
                
            unset($formConfig['fieldsets']['common']['spec']['elements']['name']);
            
            $userData['object_type_id'] = $objectTypeId;
                        
            
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if ('field_' == substr($k2, 0, 6)) {
                        $fieldId = substr($k2, 6);
                        $property = $this->objectPropertyCollection->getProperty($objectId, $fieldId); 
                        $formValues[$k][$k2] = $property->getValue();
                    } else {
                        if (isset($userData[$k2])) {
                            $formValues[$k][$k2] = $userData[$k2];
                        }
                    }                    
                }
            }
        }
                
        
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
        );   
    }
    
    protected function getBaseFormConfig()
    {
        $guid = 'user-item';
        $parentId = $this->objectTypesCollection->getTypeIdByGuid($guid);
        
        $descendantTypeIds = $this->objectTypesCollection->getDescendantTypeIds($parentId);
        
        $typeIds = array_merge(array($parentId), $descendantTypeIds);
        
        $typeOptions = array();
        foreach ($typeIds as $typeId) {
            $objectType = $this->objectTypesCollection->getType($typeId);
            $typeOptions[$typeId] = $objectType->getName();
        }
        
        
        
        $sqlRes = $this->db->query('select id, name from ' . DB_PREF . 'roles', array())->toArray();
        $roles = array();
        foreach ($sqlRes as $row) {
            $roles[$row['id']] = $row['name'];
        }
        
        $formConfig = array(
            'fieldsets' => array(
                'common' => array(
                    'spec' => array(
                        'name' => 'common',
                        'options' => array(
                            'label' => $this->translator->translate('Common params'),
                        ),
                        'elements' => array(
                            'username' => array(
                                'spec' => array(
                                    'name' => 'username',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:Username field'),
                                    ),
                                ),
                            ),
                            'email' => array(
                                'spec' => array(
                                    'name' => 'email',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:Email field'),
                                    ),
                                ),
                            ),
                            'display_name' => array(
                                'spec' => array(
                                    'name' => 'display_name',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:Display name field'),                            
                                    ),
                                ),
                            ),
                            'roles' => array(
                                'spec' => array(
                                    'name' => 'roles',
                                    'type' => 'select',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:Roles field'),
                                        'value_options' => $roles,
                                    ),
                                    'attributes' => array(
                                        'multiple' => true
                                    ),
                                ),
                            ),
                            'password' => array(
                                'spec' => array(
                                    'name' => 'password',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:Password field'),
                                    ),
                                    'attributes' => array(
                                        'type' => 'password'
                                    ),
                                ),
                            ),    
                            'passwordVerify' => array(
                                'spec' => array(
                                    'name' => 'passwordVerify',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:Password verify field'),
                                    ),
                                    'attributes' => array(
                                        'type' => 'password'
                                    ),
                                ),
                            ),    
                            'object_type_id' => array(
                                'spec' => array(
                                    'type' => 'object_type_link',
                                    'name' => 'object_type_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Users:User object type field'),
                                        'description' => $this->translator->translate('Users:User object type field description'),
                                        'value_options' => $typeOptions,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),            
            'input_filter' => array(
                'common' => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'username' => array(
                        'required'   => true,
                        'validators' => array(
                            array(
                                'name'    => 'StringLength',
                                'options' => array(
                                    'min' => 3,
                                    'max' => 255,
                                ),
                            ),
                            'NoRecordExists' => array(
                                'name' => 'Users\Validator\NoRecordExists',
                                'options' => array(
                                    'mapper' => $this->serviceManager->get('users_mapper'),
                                    'key'    => 'username'
                                ),
                            ),
                        ),
                    ),
                    'display_name' => array(
                        'required' => false,
                        'filters'    => array(array('name' => 'StringTrim')),
                        'validators' => array(
                            array(
                                'name'    => 'StringLength',
                                'options' => array(
                                    'min' => 3,
                                    'max' => 128,
                                ),
                            ),
                        ),
                    ),
                    'roles' => array(
                        'required' => true,
                    ),
                    'password' => array(
                        'name'       => 'password',
                        'required'   => true,
                        'filters'    => array(array('name' => 'StringTrim')),
                        'validators' => array(
                            array(
                                'name'    => 'StringLength',
                                'options' => array(
                                    'min' => 6,
                                ),
                            ),
                        ),
                    ),
                    'passwordVerify' => array(
                        'name'       => 'passwordVerify',
                        'required'   => true,
                        'filters'    => array(array('name' => 'StringTrim')),
                        'validators' => array(
                            array(
                                'name'    => 'StringLength',
                                'options' => array(
                                    'min' => 6,
                                ),
                            ),
                            array(
                                'name'    => 'Identical',
                                'options' => array(
                                    'token' => 'password',
                                ),
                            ),
                        ),
                    ),
                    'email' => array(
                        'required'   => true,
                        'validators' => array(
                            array(
                                'name' => 'EmailAddress'
                            ),
                            'NoRecordExists' => array(
                                'name' => 'Users\Validator\NoRecordExists',
                                'options' => array(
                                    'mapper' => $this->serviceManager->get('users_mapper'),
                                    'key'    => 'email'
                                ),
                            ),
                        ),
                    ),
                    'object_type_id' => array(
                        'required' => true,
                    ),
                ),                
            ),
        );
                
        return $formConfig;
    }
    
    public function edit($userId, $data)
    {
        $config = $this->serviceManager->get('config');
        $options = $config['Users'];
        
        $tmp = $this->getForm($userId);
        
        $formConfig = $tmp['formConfig'];
        unset($formConfig['input_filter']['common']['username']['validators']['NoRecordExists']);
        unset($formConfig['input_filter']['common']['email']['validators']['NoRecordExists']);
        if (isset($data['common']['password']) && $data['common']['password'] == '') { 
            $formConfig['input_filter']['common']['password']['required'] = false;
            $formConfig['input_filter']['common']['passwordVerify']['required'] = false;
        }
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);         
        
        $form->setData($data);
        
        $result = array();
        
        if ($form->isValid()) {
            $data = $form->getData();
            
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
            
            
            $sqlRes = $this->db->query('select * from ' . DB_PREF . 'users where user_id = ?', array($userId))->toArray();
            
            if (empty($sqlRes)) {
                throw new \Exception('user ' . $userId . ' not found');
            }
            
            $objectId = $sqlRes[0]['object_id'];
            $objectTypeId = $insertBase['object_type_id'];
            unset($insertBase['object_type_id']);
            
            $roles = $insertBase['roles'];
            unset($insertBase['roles']);
            
            $this->db->query('delete from ' . DB_PREF . 'user_role_linker where user_id = ?', array($userId));
            foreach ($roles as $roleId) {
                $this->db->query('insert into ' . DB_PREF . 'user_role_linker (user_id, role_id) values (?, ?)', array($userId, $roleId));
            }
            
            unset($insertBase['passwordVerify']);
            if ($insertBase['password'] == '') {
                unset($insertBase['password']);
            } else {
                $bcrypt = new Bcrypt;
                $bcrypt->setCost($options['passwordCost']);
                $password = $bcrypt->create($insertBase['password']);
                
                $insertBase['password'] = $password;
            }   
            
            $object = $this->objectsCollection->getObject($objectId);            
            $object->setName('user-item')->setTypeId($objectTypeId)->save();
            
            $sql = new Sql($this->db);
            $update = $sql->update($this->table)->set($insertBase)->where('user_id = ' . (int)$userId);
            $sql->prepareStatementForSqlObject($update)->execute();    

            $objectType = $this->objectTypesCollection->getType($objectTypeId); 
            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (isset($insertFields[$k2])) {
                        $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
            
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result; 
    }
    
    public function add($data)
    {
        $config = $this->serviceManager->get('config');
        $options = $config['Users'];
        
        $tmp = $this->getForm();
        
        $formConfig = $tmp['formConfig'];
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);   
        
        $form->setData($data);
        
        $result = array();
        
        if ($form->isValid()) {
            $data = $form->getData();
            
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
            $bcrypt->setCost($options['passwordCost']);
            $password = $bcrypt->create($insertBase['password']);

            $insertBase['password'] = $password;
            
            $objectId = $this->objectsCollection->addObject('user-item', $insertBase['object_type_id']);
            $insertBase['object_id'] = $objectId;
            
            $objectTypeId = $insertBase['object_type_id'];            
            unset($insertBase['object_type_id']);
            
            $roles = $insertBase['roles'];
            unset($insertBase['roles']);
            
            $sql = new Sql($this->db);
            $insert = $sql->insert($this->table)->values($insertBase);
            $sql->prepareStatementForSqlObject($insert)->execute();    

            $userId = $this->db->getDriver()->getLastGeneratedValue();
            
            $this->db->query('delete from ' . DB_PREF . 'user_role_linker where user_id = ?', array($userId));
            foreach ($roles as $roleId) {
                $this->db->query('insert into ' . DB_PREF . 'user_role_linker (user_id, role_id) values (?, ?)', array($userId, $roleId));
            }
            
            
            $objectType = $this->objectTypesCollection->getType($objectTypeId); 
            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (isset($insertFields[$k2])) {
                        $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
                                    
            $result['userId'] = $userId;
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result; 
    }
    
    public function delete($userId)
    {
        $this->db->query('
            delete from ' . $this->userRoleLinkerTable . '
            where user_id = ?
        ', array($userId));
        
        $sqlRes = $this->db->query('select object_id from ' . $this->table . '
            where user_id = ?
            ', array($userId))->toArray();
        
        
        if (!empty($sqlRes)) {
            $this->objectsCollection->delObject($sqlRes[0]['object_id'], false);
            
            $this->db->query('
                    delete from ' . $this->table . '
                    where user_id = ?
                ', array($userId));
        }
        
        return true;
    }
    
    public function register($data)
    {
        $result = array();
        
        $config = $this->serviceManager->get('config');
        
        $application = $this->serviceManager->get('application');
        
        $options = $config['Users'];  
        
        $formConfig = $this->getRegistrationFormConfig();
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
        
        $form = $factory->createForm($formConfig);   
        $form->setAttribute('method', 'post');
        $form->setData($data);
        $form->prepare();
        
        if (!$form->isValid()) {
            $result['success'] = false;
            $result['formMsg'] = $form->getMessages();
            $result['formConfig'] = $formConfig;      
            $result['formValues'] = $form->getData();
            
            return $result;
        }
        
        
        $data = $form->getData();
        
        $objectTypeId = $this->objectTypeId; 
            
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

        $originalPassword = $insertBase['password'];
        
        unset($insertBase['passwordVerify']);
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($options['passwordCost']);
        $password = $bcrypt->create($insertBase['password']);

        $insertBase['password'] = $password;

        $objectId = $this->objectsCollection->addObject('user-item', $objectTypeId);
        $insertBase['object_id'] = $objectId;

        // If user state is enabled, set the default state value
        if ($options['enableUserState']) {
            if ($options['defaultUserState']) {
                $insertBase['state'] = $options['defaultUserState'];
            }
        }
        
        $application->getEventManager()->trigger('register', $this, array('form' => $form));

        $sql = new Sql($this->db);
        $insert = $sql->insert($this->table)->values($insertBase);
        $sql->prepareStatementForSqlObject($insert)->execute();    

        $userId = $this->db->getDriver()->getLastGeneratedValue();

        $objectType = $this->objectTypesCollection->getType($objectTypeId); 
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) { 
                if ($v2->getIsVisible() && isset($insertFields[$k2])) {
                    $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }
        
        $application->getEventManager()->trigger('register.post', $this, array('userId' => $userId, 'form' => $form));

        $insertBase['password'] = $originalPassword;
        $result['userData'] = $insertBase;
        $result['userId'] = $userId;
        $result['success'] = true; 
        
        return $result;
    }
    
    public function authenticate($data)
    {
        $formConfig = $this->getLoginFormConfig();
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));

        $form = $factory->createForm($formConfig);   
        $form->setAttribute('method', 'post');
        $form->setData($data);
        $form->prepare();

        if (!$form->isValid()) {
            return false;
        }

        $authAdapter = $this->serviceManager->get('Users\Authentication\Adapter\AdapterChain');
        $authService = $this->serviceManager->get('users_auth_service');
        
        // clear adapters
        $authAdapter->resetAdapters();
        $authService->clearIdentity();
        
        $result = $authAdapter->prepareForAuthentication($this->request);
        
        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return false;
        }

        $auth = $authService->authenticate($authAdapter);

        if (!$auth->isValid()) {
            return false;
        }
        
        return true;
    }
    
    public function getRegistrationFormConfig()
    {
        $form = $this->getForm(null, true);
        
        $formConfig = $form['formConfig'];
        
        $config = $this->serviceManager->get('config');
        
        $options = $config['Users'];  
        
        unset($formConfig['fieldsets']['common']['spec']['elements']['roles']);
        unset($formConfig['fieldsets']['common']['spec']['elements']['object_type_id']);
                
        unset($formConfig['input_filter']['common']['roles']);
        unset($formConfig['input_filter']['common']['object_type_id']);
        
        if (!$options['enableUsername']) {
            unset($formConfig['fieldsets']['common']['spec']['elements']['username']);
            unset($formConfig['input_filter']['common']['username']);
        }
        if (!$options['enableDisplayName']) {
            unset($formConfig['fieldsets']['common']['spec']['elements']['display_name']);
            unset($formConfig['input_filter']['common']['display_name']);
        }
        
        return $formConfig;
    }
    
    public function getLoginFormConfig()
    {
        $config = $this->serviceManager->get('config');
        
        $options = $config['Users'];  
        
        $identityLabel = 'Users:Identity field:' . implode(' or ', $options['authIdentityFields']);
        
        $identityFields = $options['authIdentityFields'];
        $identityValidators = array();
        if ($identityFields == array('email')) {
            $identityValidators = array('name' => 'EmailAddress');
        }
        
        $formConfig = array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'identity',
                        'options' => array(
                            'label' => $this->translator->translate($identityLabel),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                            'autofocus' => 'autofocus',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'credential',
                        'options' => array(
                            'label' => $this->translator->translate('Users:Credential field'),
                        ),
                        'attributes' => array(
                            'type' => 'password',
                        ),
                    ),                    
                ),
            ),
            'input_filter' => array(
                'identity' => array(
                    'required' => true,
                    'validators' => $identityValidators,
                ),
                'credential' => array(
                    'required' => true,
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'min' => 6,
                            ),
                        ),
                    ),
                    'filters'   => array(
                        array('name' => 'StringTrim'),
                    ),
                ),
            ),
        );
        
        return $formConfig;
    }
}
