<?php

namespace App\PublicResources;

class PublicResources {

    protected $_coreResources = array();
    protected $_css = array();
    protected $_js = array();
    protected $_jsCallback = array();

    protected $serviceManager;
    
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        elseif (!is_array($options)) {
            throw new \Exception('Invalid options provided; must be location of config file, a config object, or an array');
        }
        
        $this->setOptions($options);
        
        if (null === $this->serviceManager) {
            throw new \Exception('No serviceManager in options');
        }        
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }
    
    public function setCoreResources($resources) {
        $this->_coreResources = $resources;
    }

    public function includeResources($res)
    {
        if (!empty($res['css'])) {
            $this->includeCSS($res['css']);
        }
        if (!empty($res['js'])) {
            $this->includeJS($res['js']);
        }
        if (!empty($res['jsCallback'])) {
            $this->includeJSCallback($res['jsCallback']);
        }
    }
    
    /**
     * Добавляет css файл или код в массив _TPL
     * @global array $_TPL
     * @param string|array $css 
     */
    public function includeCSS($css) {
        foreach ($css as $k => $v) {
            if (is_string($v)) {
                $v = array(
                    'link' => $v
                );
            }

            if (isset($this->_css[$k])) {
                if (isset($v['version']) && isset($this->_css[$k]['version'])) {
                    if (version_compare($v['version'], $this->_css[$k]['version']) == 1) {
                        $this->_css[$k] = $v;
                    }
                } elseif (isset($v['version'])) {
                    $this->_css[$k] = $v;
                }
            } else {
                if (isset($this->_coreResources['css'][$k])) {
                    if (isset($v['version'])) {
                        if (version_compare($v['version'], $this->_coreResources['css'][$k]['version']) == 1) {
                            $this->_css[$k] = $v;
                        } else {
                            $this->_css[$k] = $this->_coreResources['css'][$k];
                        }
                    } else {
                        $this->_css[$k] = $this->_coreResources['css'][$k];
                    }
                } else {
                    $this->_css[$k] = $v;
                }
            }
        }
    }

    /**
     * Добавляет js файл или код в массив _TPL
     * @global array $_TPL
     * @param string|array $css
     */
    public function includeJS($js) {        
        foreach ($js as $k => $v) {
            if (is_string($v)) {
                $v = array(
                    'link' => $v
                );
            }

            if (isset($this->_js[$k])) {
                if (isset($v['version']) && isset($this->_js[$k]['version'])) {
                    if (version_compare($v['version'], $this->_js[$k]['version']) == 1) {
                        $this->_js[$k] = $v;
                    }
                } elseif (isset($v['version'])) {
                    $this->_js[$k] = $v;
                }
            } else {
                if (isset($this->_coreResources['js'][$k])) {
                    if (isset($v['version'])) {
                        if (version_compare($v['version'], $this->_coreResources['js'][$k]['version']) == 1) {
                            $this->_js[$k] = $v;
                        } else {
                            $this->_js[$k] = $this->_coreResources['js'][$k];
                        }
                    } else {
                        $this->_js[$k] = $this->_coreResources['js'][$k];
                    }
                } else {
                    $this->_js[$k] = $v;
                }
            }
        }
    }

    public function includeJSCallback($js) {
        foreach ($js as $k => $v) {
            if (isset($this->_jsCallback[$k])) {
                if (isset($v['version']) && isset($this->_jsCallback[$k]['version'])) {
                    if (version_compare($v['version'], $this->_jsCallback[$k]['version']) == 1) {
                        $this->_jsCallback[$k] = $v;
                    }
                } elseif (isset($v['version'])) {
                    $this->_jsCallback[$k] = $v;
                }
            } else {
                if (isset($this->_coreResources['jsCallback'][$k])) {
                    if (isset($v['version'])) {
                        if (version_compare($v['version'], $this->_coreResources['jsCallback'][$k]['version']) == 1) {
                            $this->_jsCallback[$k] = $v;
                        } else {
                            $this->_jsCallback[$k] = $this->_coreResources['jsCallback'][$k];
                        }
                    } else {
                        $this->_jsCallback[$k] = $this->_coreResources['jsCallback'][$k];
                    }
                } else {
                    $this->_jsCallback[$k] = $v;
                }
            }
        }
    }

    public function setResourcesToView() {
        $renderer = $this->serviceManager->get('Zend\View\Renderer\PhpRenderer');        
        $renderer->headScript()->setAllowArbitraryAttributes(true);
        
        foreach ($this->_js as $k => $r) {
            $attribs = array();
            if (isset($r['conditional'])) {
                $attribs['conditional'] = $r['conditional'];
            }
            if (isset($r['link'])) {
                if (is_array($r['link'])) {
                    foreach ($r['link'] as $link) {
                        $renderer->headScript()->appendFile($link, 'text/javascript', $attribs);
                    }
                } else {
                    $renderer->headScript()->appendFile($r['link'], 'text/javascript', $attribs);
                }
            } elseif (isset($r['code'])) {
                $renderer->headScript()->appendScript($r['code'], 'text/javascript', $attribs);
            }
        }

        if (!empty($this->_jsCallback)) {
            $js_code = '';
            foreach ($this->_jsCallback as $k => $r) {
                $attribs = array();
                if (isset($r['conditional'])) {
                    $attribs['conditional'] = $r['conditional'];
                }


                foreach ($r as $r2) {
                    if (isset($r2['code'])) {
                        $js_code .= '
                            ' . $r2['code'] . '
                        ';
                    } else {
                        if (!empty($r2['args'])) {
                            $args = ', ' . json_encode($r2['args']);
                        } else {
                            $args = '';
                        }
                        $js_code .= '					
                            zen.invoke(\'' . $r2['fn'] . '\'' . $args . ');					
                        ';
                    }
                }
            }

            $renderer->inlineScript()->appendScript($js_code, 'text/javascript', $attribs);
        }



        foreach ($this->_css as $k => $r) {
            $attribs = array();
            if (isset($r['conditional'])) {
                $attribs['conditional'] = $r['conditional'];
            }
            $attribs['id'] = $k;

            if (isset($r['link'])) {
                if (is_array($r['link'])) {
                    $i = 0;

                    if (isset($r['inside_document']) && $r['inside_document'] == true) {
                        foreach ($r['link'] as $link) {
                            $i++;

                            $renderer->headStyle()->appendStyle(file_get_contents($link), $attribs);

                            $attribs['id'] = $k . '_' . $i;
                        }
                    } else {
                        foreach ($r['link'] as $link) {
                            $i++;

                            $tmp = array_merge($attribs, array('type' => 'text/css', 'rel' => 'stylesheet', 'href' => $link));
                            $renderer->headLink($tmp);

                            $attribs['id'] = ' id="' . $k . '_' . $i . '"';
                        }
                    }
                } else {
                    if (isset($r['inside_document']) && $r['inside_document'] == true) {
                        $renderer->headStyle()->appendStyle(file_get_contents($r['link']), $attribs);
                    } else {
                        $tmp = array_merge($attribs, array('type' => 'text/css', 'rel' => 'stylesheet', 'href' => $r['link']));
                        $renderer->headLink($tmp);
                    }
                }
            } elseif (isset($r['code'])) {
                $renderer->headStyle()->appendStyle($r['code'], $attribs);
            }
        }
    }

    public function getResources() {
        $resources = array();

        if (!empty($this->_js)) {
            $resources['js_include'] = array();

            foreach ($this->_js as $k => $r) {
                if (isset($r['link'])) {
                    $resources['js_include'][] = array(
                        'link' => $r['link'],
                    );
                } elseif (isset($r['code'])) {
                    $resources['js_include'][] = array(
                        'code' => $r['code'],
                    );
                }
            }
        }

        if (!empty($this->_css)) {
            $resources['css_include'] = array();

            foreach ($this->_css as $k => $r) {
                if (isset($r['link'])) {
                    $resources['css_include'][] = array(
                        'link' => $r['link'],
                    );
                } elseif (isset($r['code'])) {
                    $resources['css_include'][] = array(
                        'code' => $r['code'],
                    );
                }
            }
        }

        if (!empty($this->_jsCallback)) {
            $resources['jsCallback'] = array();

            foreach ($this->_jsCallback as $k => $r) {
                foreach ($r as $r2) {
                    if (isset($r2['fn'])) {
                        $tmp = array();
                        $tmp['fn'] = $r2['fn'];
                        if (!empty($r2['args'])) {
                            $tmp['args'] = $r2['args'];
                        }
                        $resources['jsCallback'][] = $tmp;
                    } elseif (isset($r2['code'])) {
                        $resources['jsCallback'][] = array(
                            'code' => $r2['code'],
                        );
                    }
                }
            }
        }


        return $resources;
    }

    public function prepareScriptStyleArrayToAJAX(&$data) {
        if (isset($data['js_include'])) {
            $tmp = array();
            foreach ($data['js_include'] as $k => $js_include) {
                if (isset($this->_coreResources['js'][$k])) {
                    if (isset($v['version'])) {
                        if (version_compare($v['version'], $this->_coreResources['js'][$k]['version']) == -1) {
                            $js_include = $this->_coreResources['js'][$k];
                        }
                    } else {
                        $js_include = $this->_coreResources['js'][$k];
                    }
                }

                if (is_array($js_include['link'])) {
                    foreach ($js_include['link'] as $file) {
                        $tmp[] = $file;
                    }
                } else {
                    $tmp[] = $js_include['link'];
                }
            }
            $data['js_include'] = $tmp;
        }

        if (isset($data['css_include'])) {
            $tmp = array();
            foreach ($data['css_include'] as $css_id => $css_data) {
                if (isset($this->_coreResources['css'][$css_id])) {
                    if (isset($v['version'])) {
                        if (version_compare($v['version'], $this->_coreResources['css'][$css_id]['version']) == -1) {
                            $css_data = $this->_coreResources['css'][$css_id];
                        }
                    } else {
                        $css_data = $this->_coreResources['css'][$css_id];
                    }
                }

                if (is_array($css_data['link'])) {
                    foreach ($css_data['link'] as $file) {
                        $tmp[$css_id][] = $file;
                    }
                } else {
                    $tmp[$css_id][] = $css_data['link'];
                }
            }
            $data['css_include'] = $tmp;
        }

        return $data;
    }

}