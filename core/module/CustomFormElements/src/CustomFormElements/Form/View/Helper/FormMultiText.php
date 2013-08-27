<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CustomFormElements\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormInput;

class FormMultiText extends FormInput
{
    /**
     * Attributes valid for the input tag type="text"
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'name'           => true,
        'autocomplete'   => true,
        'autofocus'      => true,
        'dirname'        => true,
        'disabled'       => true,
        'form'           => true,
        'list'           => true,
        'maxlength'      => true,
        'pattern'        => true,
        'placeholder'    => true,
        'readonly'       => true,
        'required'       => true,
        'size'           => true,
        'type'           => true,
        'value'          => true,
    );

    /**
     * Determine input type to use
     *
     * @param  ElementInterface $element
     * @return string
     */
    protected function getType(ElementInterface $element)
    {
        return 'text';
    }
    
    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $values = $element->getValue();
                
        $this->getView()->inlineScript()->appendFile(ROOT_URL_SEGMENT . '/js/CustomFormElements/multitext.js');
        
        $html = '';
        
        $html .= '<div class="multitext-items">';
        if (empty($values)) {
            $attributes          = $element->getAttributes();
            $attributes['name']  = $name . '[]';
            $attributes['type']  = $this->getType($element);
            $attributes['value'] = '';
            
            $html .= '<div class="multitext-item">';
            $html .= sprintf(
                '<input %s%s',
                $this->createAttributesString($attributes),
                $this->getInlineClosingBracket()
            );
            $html .= '<img class="icons__item icons__item-add" onclick="zen.multitext.add(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
            $html .= '<img class="icons__item icons__item-del hide" onclick="zen.multitext.del(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
            $html .= '</div>';
        } else {
            $i = 0;
            foreach ($values as $value) {
                $i++;
                
                $attributes          = $element->getAttributes();
                $attributes['name']  = $name . '[]';
                $attributes['type']  = $this->getType($element);
                $attributes['value'] = $value;
                
                $html .= '<div class="multitext-item">';
                $html .= sprintf(
                    '<input %s%s',
                    $this->createAttributesString($attributes),
                    $this->getInlineClosingBracket()
                );
                
                $html .= '<img class="icons__item icons__item-add" onclick="zen.multitext.add(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
                
                $class = '';
                if ($i == 1 && count($values) == 1) {
                    $class = ' hide';
                }
                
                $html .= '<img class="icons__item icons__item-del' . $class . '" onclick="zen.multitext.del(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
                
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
