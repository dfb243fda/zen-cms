<?php

namespace ContactForms\Form;

use Zend\Form\Element;
use App\Utility\GeneralUtility;

class Form
{
    protected $serviceManager;
    
    protected $template;    
    
    protected $viewHelperManager;
    
    protected $zendForm = array();
    
    protected $inputFilters = array();
    
    public function __construct($options)
    {
        $this->serviceManager = $options['serviceManager'];
        
        $this->template = $options['template'];
        
        $this->availableTags = array(
            'text'       => array(
                'fn' => array($this, 'getTextTag'),
                'htmlAttribs' => array('id', 'class', 'size', 'maxlength'),
            ),
            'textarea'   => array(
                'fn' => array($this, 'getTextareaTag'),
                'htmlAttribs' => array('id', 'class', 'cols', 'rows', 'maxlength'),
            ),
            'email'      => array(
                'fn' => array($this, 'getEmailTag'),
                'htmlAttribs' => array('id', 'class', 'size', 'maxlength'),
            ),
            'url'        => array(
                'fn' => array($this, 'getUrlTag'),
                'htmlAttribs' => array('id', 'class', 'size', 'maxlength'),
            ),
            'select'     => array(
                'fn' => array($this, 'getSelectTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'checkboxes' => array(
                'fn' => array($this, 'getCheckboxesTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'radio'      => array(
                'fn' => array($this, 'getRadioTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'date'       => array(
                'fn' => array($this, 'getDateTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'time'       => array(
                'fn' => array($this, 'getTimeTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'datetime'   => array(
                'fn' => array($this, 'getDateTimeTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'file'       => array(
                'fn' => array($this, 'getFileTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'captcha'    => array(
                'fn' => array($this, 'getCaptchaTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
            'submit'     => array(
                'fn' => array($this, 'getSubmitTag'),
                'htmlAttribs' => array('id', 'class'),
            ),
        );
        
        $this->viewHelperManager = $this->serviceManager->get('viewHelperManager');
        
        $factory = new \Zend\Form\Factory($this->serviceManager->get('formElementManager'));
        $this->zendForm = $factory->createForm(array());
        
        $this->template = $this->do_shortcode($this->template);
        
        
        foreach ($this->inputFilters as $name=>$filter) {
            if (isset($filter['required'])) {
                $required = $filter['required'];
                $this->zendForm->getInputFilter()->get($name)->setRequired($required)->setAllowEmpty(!$required);
            }
            if (isset($filter['validators'])) {
                foreach ($filter['validators'] as $k=>$v) {
                    $this->zendForm->getInputFilter()->get($name)->getValidatorChain()->attachByName($k, $v);
                }                
            }
        }
              
    }
    
    public function getZendForm()
    {
        return $this->zendForm;
    }
    
    public function get_shortcode_regex()
    {
        $tagnames = array_keys($this->availableTags);
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
              '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
    
    public function do_shortcode($content) {
        $pattern = $this->get_shortcode_regex();

        return preg_replace_callback( "/$pattern/s", array($this, 'do_shortcode_tag'), $content );
    }
    
    function do_shortcode_tag( $m ) {
        // allow [[foo]] syntax for escaping a tag
        if ( $m[1] == '[' && $m[6] == ']' ) {
            return substr($m[0], 1, -1);
        }

        $availableTags = $this->availableTags;
        
        $tag = $m[2];
        $attr = $this->shortcode_parse_atts( $m[3] );

        if ( isset( $m[5] ) ) {
            // enclosing tag - extra parameter
            return $m[1] . call_user_func( $availableTags[$tag]['fn'], $attr, $m[5], $tag ) . $m[6];
        } else {
            // self-closing tag            
            return $m[1] . call_user_func( $availableTags[$tag]['fn'], $attr, null, $tag ) . $m[6];
        }
    }
    
    public function getHtml()
    {        
        $template = $this->template;
        
        foreach ($this->zendForm->getElements() as $element) {
            $html = $this->viewHelperManager->get('formRow')->render($element);
            $template = str_replace('###' . $element->getName() . '###', $html, $template);
        }        
        
        return $template;
    }
    
    protected function getTagAttributesFromArray($tag, $data)
    {
        $attr = array();
        if (isset($this->availableTags[$tag])) {
            foreach ($data as $k => $v) {
                if (in_array($k, $this->availableTags[$tag]['htmlAttribs'])) {
                    $attr[$k] = $v;
                }
            }
        }
        return $attr;
    }
    
    protected function getFormElement($elClass, &$attr)
    {
        $options = array();
        
        $element = null;
        
        if (isset($attr[0])) {
            if ('*' == $attr[0]) {
                if (isset($attr[1])) {
                    $fieldName = $attr[1];
                    
                    array_shift($attr);
                    array_shift($attr);
                    $element = new $elClass($fieldName, $options);
                    
                    $this->inputFilters[$element->getName()] = array(
                        'required' => true,
                    );
                }
            } else {
                $fieldName = $attr[0];
                array_shift($attr);
                $element = new $elClass($fieldName, $options);
                $this->inputFilters[$element->getName()] = array(
                    'required' => false,
                );     
            }
        }
        
        return $element;
    }
    
    protected function getInputTag($elClass, $attr, $content, $tag)
    {        
        $element = $this->getFormElement($elClass, $attr);
                
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
                $element->setAttributes($attr);                
            }
            if (isset($attr['default'])) {
                if (isset($attr['is_placeholder']) && $attr['is_placeholder']) {
                    $element->setAttribute('placeholder', $attr['default']);
                } else {
                    $element->setValue($attr['default']);
                }
            }
                
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }
    
    public function getTextTag($attr, $content, $tag)
    {               
        return $this->getInputTag('Zend\Form\Element\Text', $attr, $content, $tag);
    }
    
    public function getTextareaTag($attr, $content, $tag)
    {       
        return $this->getInputTag('Zend\Form\Element\Textarea', $attr, $content, $tag);
    }
    
    public function getEmailTag($attr, $content, $tag)
    {
        return $this->getInputTag('Zend\Form\Element\Email', $attr, $content, $tag);
    }
    
    public function getUrlTag($attr, $content, $tag)
    {
        return $this->getInputTag('Zend\Form\Element\Url', $attr, $content, $tag);
    }
    
    public function getSelectTag($attr, $content, $tag)
    {        
        $element = $this->getFormElement('Zend\Form\Element\Select', $attr);
                
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
                $element->setAttributes($attr);                
            }
            if (isset($attr['is_multiple'])) {
                $element->setAttribute('multiple', 'multiple');
            }
            if (isset($attr['add_empty_element'])) {
                $element->setEmptyOption('');
            }
            if (isset($attr['value_options'])) {
                $valueOptions = GeneralUtility::trimExplode(',', $attr['value_options']);
                $element->setValueOptions($valueOptions);
            }
            
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
                
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }
    
    public function getCheckboxesTag($attr, $content, $tag)
    {        
        $element = $this->getFormElement('Zend\Form\Element\MultiCheckbox', $attr);
                
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
                $element->setAttributes($attr);                
            }
            if (isset($attr['value_options'])) {
                $valueOptions = GeneralUtility::trimExplode(',', $attr['value_options']);
                $element->setValueOptions($valueOptions);
            }
                
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
            
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }
    
    public function getRadioTag($attr, $content, $tag)
    {        
        $element = $this->getFormElement('Zend\Form\Element\Radio', $attr);
                
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
                $element->setAttributes($attr);                
            }
            if (isset($attr['value_options'])) {
                $valueOptions = GeneralUtility::trimExplode(',', $attr['value_options']);
                $element->setValueOptions($valueOptions);
            }
            
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
                
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }    
    
    public function getDateTag($attr, $content, $tag)
    {               
        $element = $this->getFormElement('CustomFormElements\Form\Element\DatePicker', $attr);
        
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
 /*               if (isset($htmlAttr['min'])) {
                    $element->setJsAttribute('minDate', $htmlAttr['min']);
                }
                if (isset($htmlAttr['max'])) {
                    $element->setJsAttribute('maxDate', $htmlAttr['max']);
                }
*/
                $element->setAttributes($attr);                
            }
            
            if (isset($attr['default'])) {
                if (isset($attr['is_placeholder']) && $attr['is_placeholder']) {
                    $element->setAttribute('placeholder', $attr['default']);
                } else {
                    $element->setValue($attr['default']);
                }
            }
                
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }    
    
    public function getTimeTag($attr, $content, $tag)
    {               
        $element = $this->getFormElement('CustomFormElements\Form\Element\TimePicker', $attr);
        
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
  /*              if (isset($htmlAttr['min'])) {
                    $element->setJsAttribute('minDate', $htmlAttr['min']);
                }
                if (isset($htmlAttr['max'])) {
                    $element->setJsAttribute('maxDate', $htmlAttr['max']);
                }
*/
                $element->setAttributes($attr);                
            }
            
            if (isset($attr['default'])) {
                if (isset($attr['is_placeholder']) && $attr['is_placeholder']) {
                    $element->setAttribute('placeholder', $attr['default']);
                } else {
                    $element->setValue($attr['default']);
                }
            }
                
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }    
    
    public function getDateTimeTag($attr, $content, $tag)
    {               
        $element = $this->getFormElement('CustomFormElements\Form\Element\DateTimePicker', $attr);
        
        if (null !== $element) {
            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
 /*               if (isset($htmlAttr['min'])) {
                    $element->setJsAttribute('minDate', $htmlAttr['min']);
                }
                if (isset($htmlAttr['max'])) {
                    $element->setJsAttribute('maxDate', $htmlAttr['max']);
                }
*/
                $element->setAttributes($attr);                
            }
            
            if (isset($attr['default'])) {
                if (isset($attr['is_placeholder']) && $attr['is_placeholder']) {
                    $element->setAttribute('placeholder', $attr['default']);
                } else {
                    $element->setValue($attr['default']);
                }
            }
                
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }        
    
    public function getFileTag($attr, $content, $tag)
    {
        $element = $this->getFormElement('Zend\Form\Element\File', $attr);
        
        if (null !== $element) {            
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
                $element->setAttributes($attr);                
            }
            
            if (isset($attr['size'])) {
                $this->inputFilters[$element->getName()]['validators']['FileSize'] = array('max' => $attr['size']);
            }
            if (isset($attr['ext'])) {
                $this->inputFilters[$element->getName()]['validators']['FileExtension'] = array('extension' => $attr['ext']);
            }
           
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }
    
    public function getCaptchaTag($attr, $content, $tag)
    {
        $element = $this->getFormElement('Zend\Form\Element\Captcha', $attr);
        
        if (null !== $element) {
            $element->setCaptcha(new \Zend\Captcha\Dumb());
            $htmlAttr = $this->getTagAttributesFromArray($tag, $attr);
            
            if (!empty($htmlAttr)) {
                $element->setAttributes($attr);                
            }
            
            $this->inputFilters[$element->getName()] = true;
                
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        
        return '[wrong tag]';
    }
    
    public function getSubmitTag($attr, $content, $tag)
    {    
        $options = array();
        if (isset($attr[0])) {
            $value = $attr[0];
            $element = new Element\Submit('submit', $options);            
            $element->setAttribute('value', $value);
            
            $this->zendForm->add($element);
            return '###' . $element->getName() . '###';
//            return $this->viewHelperManager->get('formElement')->render($element);
        }
        return '[wrong tag]';
    }
    
    function shortcode_parse_atts($text) {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) and strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }
}