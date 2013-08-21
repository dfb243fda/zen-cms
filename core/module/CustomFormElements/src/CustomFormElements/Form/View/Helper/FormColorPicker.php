<?php

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\View\Helper\FormText;
use Zend\Form\ElementInterface;

class FormColorPicker extends FormText
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
        if ($attributes['value']) {
            $attributes['style'] = 'background-color: ' . $attributes['value'];
        }
        
        $jsAttributes = $element->getJsAttributes();
        
        $jsAttributes['onSubmit'] = new \Zend\Json\Expr("function(hsb, hex, rgb, el) {
            $(el).val('#' + hex);
            $(el).ColorPickerHide();
        }");

        $jsAttributes['onBeforeShow'] = new \Zend\Json\Expr("function() {
            $(this).ColorPickerSetColor(this.value);
        }");
        
        $jsAttributes['onChange'] = new \Zend\Json\Expr("function (hsb, hex, rgb) {
            $('#" . $attributes['id'] . "').css('backgroundColor', '#' + hex);
        }");
        
        $jsAttributesStr = \Zend\Json\Json::encode(
            $jsAttributes,
            false,
            array('enableJsonExprFinder' => true)
        );
        
        $escape     = $this->getEscapeHtmlHelper();
        
        $this->getView()->headScript()->appendFile(ROOT_URL_SEGMENT . '/js/CustomFormElements/colorpicker/js/colorpicker.js');
        $this->getView()->inlineScript()->appendScript('$(\'#'.$escape($id).'\').ColorPicker('.$jsAttributesStr.').bind(\'keyup\', function(){
	$(this).ColorPickerSetColor(this.value);
});');
        
        $this->getView()->headLink()->appendStylesheet(ROOT_URL_SEGMENT . '/js/CustomFormElements/colorpicker/css/colorpicker.css');
        
        return sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $this->getInlineClosingBracket()
        );
    }
}