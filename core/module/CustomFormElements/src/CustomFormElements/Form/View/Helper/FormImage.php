<?php

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\View\Helper\FormText;
use Zend\Form\ElementInterface;

class FormImage extends FormText
{
    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }
        
        $attributes          = $element->getAttributes();
        if (isset($attributes['id'])) {
            $id = $attributes['id'];
        } else {
            $id = str_replace(array('[', ']'), '_', $name);
            $attributes['id'] = $id;
        }
        $attributes['name']  = $name;
        $attributes['type']  = $this->getType($element);
        $attributes['value'] = $element->getValue();
        
        $escape = $this->getEscapeHtmlHelper();
                
        $jsAttr = array(
            'url' => $this->view->url('elfinder', array(), array(
                'query' => array(
                    'dirs' => 'img,files,uploads',
                )
            )),
            'getFileCallback' => new \Zend\Json\Expr('function(url) {
                $(\'#' . $escape($id) . '\').val(url);
                $.fancybox.close();
            }'),
        );
        
        $jsAttrStr = \Zend\Json\Json::encode(
            $jsAttr,
            false,
            array('enableJsonExprFinder' => true)
        );
		
        $this->getView()->headLink()->appendStylesheet(ROOT_URL_SEGMENT . '/js/core/file_manager/elfinder-2.0-rc1/css/elfinder.min.css');
        $this->getView()->headScript()->appendFile(ROOT_URL_SEGMENT . '/js/core/file_manager/elfinder-2.0-rc1/js/elfinder.min.js');
        $this->getView()->inlineScript()->appendScript('
            $(\'#icon-' . $escape($id) . '\').fancybox({
                content : \'<div id="fancybox-' . $escape($id) . '"></div>\',
                afterShow: function() { $(\'#fancybox-' . $escape($id) . '\').elfinder(' . $jsAttrStr . '); },                            
            });
        ');
        
        
        return sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $this->getInlineClosingBracket()
        ) . '<img id="icon-' . $escape($id) . '" class="input-icon" src="' . ROOT_URL_SEGMENT . '/img/CustomFormElements/browse_folder_icon.png" alt="" />';
    }
}


