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
use Zend\Stdlib\ArrayUtils;

class FormComposite extends FormInput
{
    /**
     * Attributes valid for select
     *
     * @var array
     */
    protected $validSelectAttributes = array(
        'name'      => true,
        'autofocus' => true,
        'disabled'  => true,
        'form'      => true,
        'multiple'  => true,
        'required'  => true,
        'size'      => true
    );

    /**
     * Attributes valid for option groups
     *
     * @var array
     */
    protected $validOptgroupAttributes = array(
        'disabled' => true,
        'label'    => true,
    );

    protected $translatableAttributes = array(
        'label' => true,
    );
    
    /**
     * Attributes valid for the input tag type="text"
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'disabled' => true,
        'selected' => true,
        'label'    => true,
        'value'    => true,
        
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
    
    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $options = $element->getValueOptions();
        
        if (!isset($options[''])) {
            $options = array('' => '') + $options;
        }
        
        $values = $element->getValue();
                
        $this->getView()->inlineScript()->appendFile(ROOT_URL_SEGMENT . '/js/CustomFormElements/composite.js');
        
        $html = '';
            
        $html .= '<div class="composite-items">';
        if (empty($values['object_rel'])) {
            $attributes1          = $element->getAttributes();
            $attributes1['name']  = $name . '[object_rel][]';
            $attributes1['type']  = $this->getType($element);
            
            $attributes2          = $element->getAttributes();
            unset($attributes2['id']);
            $attributes2['name']  = $name . '[varchar][]';
            $attributes2['type']  = 'text';
            $attributes2['value'] = '';
            
            $html .= '<div class="composite-item">';
            
            $html .= sprintf(
                '<select %s>%s</select>',
                $this->createAttributesString($attributes1),
                $this->renderOptions($options, array())
            );
            
            $html .= sprintf(
                '<input %s%s',
                $this->createAttributesString($attributes2),
                $this->getInlineClosingBracket()
            );
            $html .= '<img class="icons__item icons__item-add" onclick="zen.composite.add(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
            $html .= '<img class="icons__item icons__item-del hide" onclick="zen.composite.del(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
            $html .= '</div>';
        } else {            
            $i = 0;
            foreach ($values['object_rel'] as $k=>$value) {
                if (!isset($values['varchar'][$k])) {
                    continue;
                }
                
                $i++;
                
                $attributes1          = $element->getAttributes();
                $attributes1['name']  = $name . '[object_rel][]';
                $attributes1['type']  = $this->getType($element);

                $attributes2          = $element->getAttributes();
                unset($attributes2['id']);
                $attributes2['name']  = $name . '[varchar][]';
                $attributes2['type']  = 'text';
                $attributes2['value'] = $values['varchar'][$k];
                
                $html .= '<div class="composite-item">';
                $html .= sprintf(
                    '<select %s>%s</select>',
                    $this->createAttributesString($attributes1),
                    $this->renderOptions($options, array($value))
                );

                $html .= sprintf(
                    '<input %s%s',
                    $this->createAttributesString($attributes2),
                    $this->getInlineClosingBracket()
                );
                
                $html .= '<img class="icons__item icons__item-add" onclick="zen.composite.add(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
                
                $class = '';
                if ($i == 1 && count($values['object_rel']) == 1) {
                    $class = ' hide';
                }
                
                $html .= '<img class="icons__item icons__item-del' . $class . '" onclick="zen.composite.del(this)" src="' . ROOT_URL_SEGMENT . '/img/core/pixel.gif" />';
                
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    public function renderOptions(array $options, array $selectedOptions = array())
    {
        $template      = '<option %s>%s</option>';
        $optionStrings = array();
        $escapeHtml    = $this->getEscapeHtmlHelper();

        foreach ($options as $key => $optionSpec) {
            $value    = '';
            $label    = '';
            $selected = false;
            $disabled = false;

            if (is_scalar($optionSpec)) {
                $optionSpec = array(
                    'label' => $optionSpec,
                    'value' => $key
                );
            }

            if (isset($optionSpec['options']) && is_array($optionSpec['options'])) {
                $optionStrings[] = $this->renderOptgroup($optionSpec, $selectedOptions);
                continue;
            }

            if (isset($optionSpec['value'])) {
                $value = $optionSpec['value'];
            }
            if (isset($optionSpec['label'])) {
                $label = $optionSpec['label'];
            }
            if (isset($optionSpec['selected'])) {
                $selected = $optionSpec['selected'];
            }
            if (isset($optionSpec['disabled'])) {
                $disabled = $optionSpec['disabled'];
            }

            if (ArrayUtils::inArray($value, $selectedOptions)) {
                $selected = true;
            }

            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label, $this->getTranslatorTextDomain()
                );
            }

            $attributes = compact('value', 'selected', 'disabled');

            if (isset($optionSpec['attributes']) && is_array($optionSpec['attributes'])) {
                $attributes = array_merge($attributes, $optionSpec['attributes']);
            }

            $optionStrings[] = sprintf(
                $template,
                $this->createAttributesString($attributes),
                $escapeHtml($label)
            );
        }

        return implode("\n", $optionStrings);
    }
    
    /**
     * Render an optgroup
     *
     * See {@link renderOptions()} for the options specification. Basically,
     * an optgroup is simply an option that has an additional "options" key
     * with an array following the specification for renderOptions().
     *
     * @param  array $optgroup
     * @param  array $selectedOptions
     * @return string
     */
    public function renderOptgroup(array $optgroup, array $selectedOptions = array())
    {
        $template = '<optgroup%s>%s</optgroup>';

        $options = array();
        if (isset($optgroup['options']) && is_array($optgroup['options'])) {
            $options = $optgroup['options'];
            unset($optgroup['options']);
        }

        $this->validTagAttributes = $this->validOptgroupAttributes;
        $attributes = $this->createAttributesString($optgroup);
        if (!empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        return sprintf(
            $template,
            $attributes,
            $this->renderOptions($options, $selectedOptions)
        );
    }
}
