<?php

namespace App\Mvc\I18n\Translator;

//use Zend\I18n\Translator\Translator as zendTranslator;
use Zend\Mvc\I18n\Translator as ZendTranslator;
use Zend\Validator\Translator\TranslatorInterface;

class Translator extends ZendTranslator implements TranslatorInterface
{
    public function translateI18n($message, $textDomain = 'default', $locale = null)
    {
        if ('i18n::' == substr($message, 0, 6)) {
            $message = $this->translate(substr($message, 6), $textDomain, $locale);
        }        
        return $message;
    }
}