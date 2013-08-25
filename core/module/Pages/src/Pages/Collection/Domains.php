<?php

namespace Pages\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\Utility\GeneralUtility;

class Domains implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getForm()
    {
        $form = $this->serviceManager->get('Pages\Form\Domain');
                
        $form->init();
        
        return $form;
    }
    
    public function addDomain($data)
    {
        $db = $this->serviceManager->get('db');
        
        if ($data['is_default']) {
            $db->query('update ' . DB_PREF . 'domains set is_default = 0', array());
        }

        $db->query('
            insert ignore into ' . DB_PREF . 'domains
            (host, is_default, default_lang_id)
            values (?, ?, ?)', array($data['host'], $data['is_default'], $data['default_lang_id']));

        $domainId = $db->getDriver()->getLastGeneratedValue();

        $domainMirrors = GeneralUtility::trimExplode(LF, $data['domain_mirrors'], true);

        foreach ($domainMirrors as $host) {
            $db->query('
                insert into ' . DB_PREF . 'domain_mirrors (host, rel) values (?, ?)
                on duplicate key update rel = ?', array($host, $domainId, $domainId));
        }

        return $domainId;   
    }
    
    public function deleteDomain($domainId)
    {
        $db = $this->serviceManager->get('db');
        
        $db->query('delete from ' . DB_PREF . 'domains where id = ?', array($domainId));
        
        $db->query('delete from ' . DB_PREF . 'domain_mirrors where rel = ?', array($domainId));
        
        return true;
    }
}
