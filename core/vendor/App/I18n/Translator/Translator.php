<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace App\I18n\Translator;

use Zend\I18n\Translator\Translator as zendTranslator;
use Zend\Validator\Translator\TranslatorInterface;

class Translator extends zendTranslator implements TranslatorInterface
{
    public function translateI18n($message, $textDomain = 'default', $locale = null)
    {
        if ('i18n::' == substr($message, 0, 6)) {
            $message = $this->translate(substr($message, 6), $textDomain, $locale);
        }        
        return $message;
    }
}