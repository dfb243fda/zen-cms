<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace App\I18n\View\Helper;

use Zend\I18n\View\Helper\Translate;

/**
 * View helper for translating messages.
 */
class TranslateI18n extends Translate
{
    /**
     * Translate a message.
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     * @throws Exception\RuntimeException
     */
    public function __invoke($message, $textDomain = null, $locale = null)
    {
        if ('i18n::' == substr($message, 0, 6)) {
            $message = substr($message, 6);
            return parent::__invoke($message, $textDomain, $locale);;
        }     
        
        return $message;
    }
}
