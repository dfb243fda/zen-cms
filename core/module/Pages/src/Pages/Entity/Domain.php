<?php

namespace Pages\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\Utility\GeneralUtility;

class Domain implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $domainId;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
    }
    
    public function getForm()
    {
        $db = $this->serviceManager->get('db');
        
        $form = $this->serviceManager->get('Pages\Form\Domain');
                
        $form->init();
        
        $domainId = $this->domainId;

        $sqlRes = $db->query('select host, is_default, default_lang_id from ' . DB_PREF . 'domains where id = ?', array($domainId))->toArray();
        if (empty($sqlRes)) {
            throw new \Exception('domain ' . $domainId . ' not found');
        }

        $sqlRes2 = $db->query('select host from ' . DB_PREF . 'domain_mirrors where rel = ?', array($domainId))->toArray();
        $mirrorsArr = array();
        foreach ($sqlRes2 as $row) {
            $mirrorsArr[] = $row['host'];
        }

        $formData = array(
            'host' => $sqlRes[0]['host'],
            'is_default' => $sqlRes[0]['is_default'],
            'default_lang_id' => $sqlRes[0]['default_lang_id'],
            'domain_mirrors' => implode(LF, $mirrorsArr),
        );

        $form->setData($formData);
        
        return $form;
    }
    
    public function editDomain($data)
    {     
        $domainId = $this->domainId;
        
        $db = $this->serviceManager->get('db');
        
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

        return true;
    }
}