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

class FormCkEditor extends FormTextarea
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
        
        if (!isset($attributes['id'])) {
            $attributes['id'] = 'ckeditor_' . str_replace(array('[', ']'), '_', $name);
        }
   
        $attributes['name'] = $name;
        $content            = (string) $element->getValue();
        $escapeHtml         = $this->getEscapeHtmlHelper();

        $this->getView()->inlineScript()->appendFile(ROOT_URL_SEGMENT . '/js/core/wysiwyg/ckeditor_4.0.1/ckeditor.js'); 
        
        $this->getView()->inlineScript()->appendScript('
            var ck_instance = CKEDITOR.instances[\''.$escapeHtml($attributes['id']).'\'];
            if(zen.isDefined(ck_instance)) {
				ck_instance.destroy();
			}
			CKEDITOR.replace(\''.$escapeHtml($attributes['id']).'\');
        ');
        
        return sprintf(
            '<textarea %s>%s</textarea>',
            $this->createAttributesString($attributes),
            $escapeHtml($content)
        );
    }
}