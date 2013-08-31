<?php

namespace Bootstrapper\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class DynamicConfig implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getConfig()
    {
        $sm = $this->serviceManager;
        $db = $sm->get('db');
        

        $sqlRes = $db->query('select prefix, title from ' . DB_PREF . 'langs', array())->toArray();

        $languages = array();
        foreach ($sqlRes as $row) {
            $languages[$row['prefix']] = $row['title'];
        }

        $translator = $sm->get('translator');
        
        $configManager = $sm->get('configManager');
        
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

        return array(
            'form' => array(
                'general' => array(
                    'fieldsets' => array(
                        'system' => array(
                            'spec' => array(
                                'name' => 'system',
                                'elements' => array(
                                    'language' => array(
                                        'spec' => array(
                                            'type' => 'select',
                                            'name' => 'language',
                                            'options' => array(
                                                'label' => 'i18n::Dynamic config system language',
                                                'description' => 'i18n::Dynamic config system language description',
                                                'value_options' => $languages,
                                            ),
                                        ),
                                    ),
                                    'timezone' => array(
                                        'spec' => array(
                                            'type' => 'select',
                                            'name' => 'timezone',
                                            'options' => array(
                                                'label' => 'i18n::Dynamic config system timezone',
                                                'description' => sprintf($translator->translate('Dynamic config system timezone description'), $utcTime, $localTime),
                                                'value_options' => $timeZones,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'input_filter' => array(
                        'system' => array(
                            'type' => 'Zend\InputFilter\InputFilter',
                            'timezone' => array(
                                'required' => true,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
    
}