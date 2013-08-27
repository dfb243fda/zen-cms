<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\View\Helper\FormTextarea;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class FormAceEditor extends FormTextarea
{
    public function render(ElementInterface $element)
    {
        $name   = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }
        
        $attributes         = $element->getAttributes();
        $options            = $element->getOptions();
        
        if (!isset($attributes['id'])) {
            $attributes['id'] = 'aceeditor_' . str_replace(array('[', ']'), '_', $name);
        }
   
        $attributes['name'] = $name;
        $content            = (string) $element->getValue();
        $escapeHtml         = $this->getEscapeHtmlHelper();

        $this->getView()->inlineScript()->appendFile(ROOT_URL_SEGMENT . '/js/core/code_editor/ace/build/src-min-noconflict/ace.js'); 
        
        if (isset($options['theme'])) {
            $theme = $options['theme'];
            unset($options['theme']);
        } else {
            $theme = 'dreamweaver';
        }
        
        if (isset($options['mode'])) {
            $mode = $options['mode'];
            unset($options['mode']);
        } else {
            $mode = 'plain_text';
        }
        
        $idAttr = $attributes['id'];
        
        $this->getView()->inlineScript()->appendScript('
            var textarea' . $name . ' = $(\'#' . $escapeHtml($idAttr) . '\').hide();
                
            var editor' . $name . ' = ace.edit(\''.$escapeHtml($idAttr . '_wrap').'\');
            editor' . $name . '.setTheme("ace/theme/' . $theme . '");
            editor' . $name . '.getSession().setMode("ace/mode/' . $mode . '");
            editor' . $name . '.getSession().setValue(textarea' . $name . '.val());
            editor' . $name . '.getSession().on(\'change\', function(){
              textarea' . $name . '.val(editor' . $name . '.getSession().getValue());
            });
        ');
        
        $textAreaAttr = $attributes;
        $divAttr = array(
            'id' => $attributes['id'] . '_wrap',
        );
        
        return sprintf(
            '<textarea %s>%s</textarea><div %s></div>',
            $this->createAttributesString($textAreaAttr),
            $escapeHtml($content),
            $this->createAttributesString($divAttr)
        );
    }
}