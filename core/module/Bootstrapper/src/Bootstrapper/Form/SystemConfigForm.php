<?php

namespace Bootstrapper\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class SystemConfigForm extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function init()
    {
        $serviceManager = $this->serviceLocator->getServiceLocator();
        
        $db = $serviceManager->get('db');
        

        $sqlRes = $db->query('select prefix, title from ' . DB_PREF . 'langs', array())->toArray();

        $languages = array();
        foreach ($sqlRes as $row) {
            $languages[$row['prefix']] = $row['title'];
        }

        $translator = $serviceManager->get('translator');
        
        $configManager = $serviceManager->get('configManager');
        
        $continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');

        $zonen = array();
        foreach (timezone_identifiers_list() as $zone) {
            $zone = explode('/', $zone);
            if (!in_array($zone[0], $continents)) {
                continue;
            }

            // This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
            $exists = array(
                0 => ( isset($zone[0]) && $zone[0] ),
                1 => ( isset($zone[1]) && $zone[1] ),
                2 => ( isset($zone[2]) && $zone[2] ),
            );
            $exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
            $exists[4] = ( $exists[1] && $exists[3] );
            $exists[5] = ( $exists[2] && $exists[3] );

            $zonen[] = array(
                'continent' => ( $exists[0] ? $zone[0] : '' ),
                'city' => ( $exists[1] ? $zone[1] : '' ),
                'subcity' => ( $exists[2] ? $zone[2] : '' ),
                't_continent' => ( $exists[3] ? $translator->translate(str_replace('_', ' ', $zone[0])) : '' ),
                't_city' => ( $exists[4] ? $translator->translate(str_replace('_', ' ', $zone[1])) : '' ),
                't_subcity' => ( $exists[5] ? $translator->translate(str_replace('_', ' ', $zone[2])) : '' )
            );
        }
                
        $timeZones = array();
        $i = 0;
        foreach ( $zonen as $key => $zone ) {
            // Build value in an array to join later
            $value = array( $zone['continent'] );

            if ( empty( $zone['city'] ) ) {
                // It's at the continent level (generally won't happen)
                $display = $zone['t_continent'];
            } else {
                // It's inside a continent group

                // Continent optgroup
                if ( !isset( $zonen[$key - 1] ) || $zonen[$key - 1]['continent'] !== $zone['continent'] ) {
                    $label = $zone['t_continent'];
                    $timeZones[$i] = array(
                        'label' => $label
                    );
                }

                // Add the city to the value
                $value[] = $zone['city'];

                $display = $zone['t_city'];
                if ( !empty( $zone['subcity'] ) ) {
                    // Add the subcity to the value
                    $value[] = $zone['subcity'];
                    $display .= ' - ' . $zone['t_subcity'];
                }
            }

            // Build the value
            $value = join( '/', $value );
            $timeZones[$i]['options'][$value] = $display;

            // Close continent optgroup
            if ( !empty( $zone['city'] ) && ( !isset($zonen[$key + 1]) || (isset( $zonen[$key + 1] ) && $zonen[$key + 1]['continent'] !== $zone['continent']) ) ) {
                $i++;
            }
        }
        

        $format = $configManager->get('system', 'date_format') . ' ' . $configManager->get('system', 'time_format');

        $dateTime = new \DateTime();

        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $utcTime = $dateTime->format($format);

        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $localTime = $dateTime->format($format);
   
        
        $this->add(array(
            'name' => 'system',
            'type' => 'fieldset',
        ));
        
        $this->get('system')->add(array(
            'name' => 'language',
            'type' => 'select',
            'options' => array(
                'label' => 'Dynamic config system language',
                'description' => 'Dynamic config system language description',
                'value_options' => $languages,
            ),
        ));
        
        $this->get('system')->add(array(
            'name' => 'timezone',
            'type' => 'select',
            'options' => array(
                'label' => 'Dynamic config system timezone',
                'description' => sprintf($translator->translate('Dynamic config system timezone description'), $utcTime, $localTime),
                'value_options' => $timeZones,
            ),
        ));
        
        $this->getInputFilter()
             ->get('system')
             ->get('timezone')
             ->setRequired(true);        
    }    
}