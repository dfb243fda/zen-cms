<?php

namespace Pages\Model;

use Zend\Form\Factory;
use App\Utility\GeneralUtility;

class Domains
{
    protected $serviceManager;
    
    protected $configManager;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->configManager = $sm->get('configManager');
        $this->db = $sm->get('db');
        $this->translator = $sm->get('translator');
    }
 
    public function getDomains()
    {
        $items = $this->db
                ->query('
                    select id, host as name from ' . DB_PREF . 'domains order by is_default desc
                    ', array())
                ->toArray();
        
        foreach ($items as $k=>$row) {
            $items[$k]['state'] = 'open';
        }
        
        return $items;
    }
    
    public function edit($domainId, $data)
    {     
        $db = $this->serviceManager->get('db');
        
        $tmp = $this->getForm($domainId);
        
        $formConfig = $tmp['formConfig'];
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);   
        
        $form->setData($data);
        
        $result = array();
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            if ($data['is_default']) {
                $db->query('update ' . DB_PREF . 'domains set is_default = 0', array());
            }
            
            $db->query('
                update ' . DB_PREF . 'domains
                set host = ?, is_default = ?, default_lang_id = ?
                where id = ?', array($data['host'], $data['is_default'], $data['default_lang_id'], $domainId));
            
            $domainMirrors = GeneralUtility::trimExplode(LF, $data['domain_mirrors'], true);
            
            $sqlRes = $db->query('select id, host from ' . DB_PREF . 'domain_mirrors where rel = ?', array($domainId))->toArray();
            foreach ($sqlRes as $row) {
                if (!in_array($row['host'], $domainMirrors)) {
                    $db->query('delete from ' . DB_PREF . 'domain_mirrors where id = ?', array($row['id']));
                }
            }
            
            foreach ($domainMirrors as $host) {
                $db->query('
                    insert into ' . DB_PREF . 'domain_mirrors (host, rel) values (?, ?)
                    on duplicate key update rel = ?', array($host, $domainId, $domainId));
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
        $db = $this->serviceManager->get('db');
        
        $tmp = $this->getForm();
        
        $formConfig = $tmp['formConfig'];
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);   
        
        $form->setData($data);
        
        $result = array();
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            if ($data['is_default']) {
                $db->query('update ' . DB_PREF . 'domains set is_default = 0', array());
            }
            
            $db->query('
                insert into ' . DB_PREF . 'domains
                (host, is_default, default_lang_id)
                values (?, ?, ?)', array($data['host'], $data['is_default'], $data['default_lang_id']));
            
            $domainId = $db->getDriver()->getLastGeneratedValue();
            
            $domainMirrors = GeneralUtility::trimExplode(LF, $data['domain_mirrors'], true);
            
            foreach ($domainMirrors as $host) {
                $db->query('
                    insert into ' . DB_PREF . 'domain_mirrors (host, rel) values (?, ?)
                    on duplicate key update rel = ?', array($host, $domainId, $domainId));
            }
            
            $result['domainId'] = $domainId;
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }
        $result['form'] = $form;
        
        return $result;
    }
    
    public function delete($domainId)
    {
        $db = $this->serviceManager->get('db');
        
        $db->query('delete from ' . DB_PREF . 'domains where id = ?', array($domainId));
        
        $db->query('delete from ' . DB_PREF . 'domain_mirrors where rel = ?', array($domainId));
        
        return true;
    }
    
    public function getForm($domainId = null)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select id, title from ' . DB_PREF . 'langs', array())->toArray();
        $langs = array();
        foreach ($sqlRes as $row) {
            $langs[$row['id']] = $row['title'];
        }
        
        if (null === $domainId) {
            $formValues = array();
        } else {
            $sqlRes = $db->query('select host, is_default, default_lang_id from ' . DB_PREF . 'domains where id = ?', array($domainId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('domain ' . $domainId . ' not found');
            }
            
            $sqlRes2 = $db->query('select host from ' . DB_PREF . 'domain_mirrors where rel = ?', array($domainId))->toArray();
            $mirrorsArr = array();
            foreach ($sqlRes2 as $row) {
                $mirrorsArr[] = $row['host'];
            }
            
            $formValues = array(
                'host' => $sqlRes[0]['host'],
                'is_default' => $sqlRes[0]['is_default'],
                'default_lang_id' => $sqlRes[0]['default_lang_id'],
                'domain_mirrors' => implode(LF, $mirrorsArr),
            );
        }
        
        $formConfig = array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'host',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:Domain host field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'checkbox',
                        'name' => 'is_default',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:Domain is default field'),
                            'value' => 1,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'select',
                        'name' => 'default_lang_id',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:Domain default lang field'),
                            'value_options' => $langs,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'textarea',
                        'name' => 'domain_mirrors',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:Domain mirrors field'),
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'host' => array(
                    'required' => true,
                ),
                'default_lang_id' => array(
                    'required' => true,
                ),
            ),
        );
        
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
        );
    }
}