<?php

class App_Payment_Robokassa extends App_Payment_Abstract
{    
    protected $_test_payment_base_url = 'http://test.robokassa.ru/Index.aspx';
    protected $_real_payment_base_url = 'http://robokassa.ru/Index.aspx';
    protected $_login;
    protected $_pass1;
    protected $_pass2;
    protected $_language = 'ru';
    protected $_payment_base_url;
    protected $_xml_base_url = 'https://merchant.roboxchange.com/WebService/Service.asmx/';
    protected $_result_url_method = 'POST';
    protected $_success_url_method = 'POST';
    protected $_fail_url_method = 'POST';
    protected $_payment_system = 'robokassa';
    protected $_default_currency = 'RUR';
    protected $_default_reserve_timeout = 86400; // 24 часа

    protected $_user_params = array();
    
    const STATUS_CANCELLED = 10;

    protected $_rabokassa_status_msg = array(
        0 => 'Не удалось получить информацию о статусе операции',
        5 => 'операция только инициализирована, деньги от покупателя не получены',
        self::STATUS_CANCELLED => 'операция отменена, деньги от покупателя не были получены',
        50 => 'деньги от покупателя получены, производится зачисление денег на счет магазина',
        60 => 'деньги после получения были возвращены покупателю',
        80 => 'исполнение операции приостановлено',
        100 => 'операция выполнена, завершена успешно',
    );

    public function __construct($options = array())
    {
        if (!isset($options['login']))
        {
            throw new App_Payment_Exception('The login option does not exists');
        }

        if (!isset($options['pass1']))
        {
            throw new App_Payment_Exception('The pass1 option does not exists');
        }

        if (!isset($options['pass2']))
        {
            throw new App_Payment_Exception('The pass2 option does not exists');
        }
        if (!isset($options['currency']))
        {
            $options['currency'] = $this->_default_currency;
        }
        if (!isset($options['reserveTimeout']))
        {
            $options['reserveTimeout'] = $this->_default_reserve_timeout;
        }

        parent::__construct($options);

        if ($this->_payment_base_url === null)
        {
            if ($this->_test_mode == true)
            {
                $this->setPaymentBaseUrl($this->_test_payment_base_url);
            }
            else
            {
                $this->setPaymentBaseUrl($this->_real_payment_base_url);
            }
        }
    }

    public function setPaymentBaseUrl($url)
    {
        $this->_payment_base_url = $url;
        return $this;
    }

    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }

    public function setPass1($pass)
    {
        $this->_pass1 = $pass;
        return $this;
    }

    public function setPass2($pass)
    {
        $this->_pass2 = $pass;
        return $this;
    }

    public function setResultUrlMethod($method)
    {
        $this->_result_url_method = $method;
        return $this;
    }

    public function setSuccessUrlMethod($method)
    {
        $this->_success_url_method = $method;
        return $this;
    }

    public function setFailUrlMethod($method)
    {
        $this->_fail_url_method = $method;
        return $this;
    }

    public function setXmlBaseUrl($url)
    {
        $this->_xml_base_url = $url;
        return $this;
    }

    public function getPaymentUrl()
    {
        $order_id = $this->getOrderId();

        if ($order_id === null)
        {
            throw new Payment_Exception('The order does not exists');
        }

        $signature_str = $this->_login . ':' . $this->_sum_of_order . ':' . $order_id . ':' . $this->_pass1;
        
        foreach ($this->_user_params as $k=>$v)
        {
            $signature_str .= ':' . $k . '=' . $v;
        }
        
        $signature_value = md5($signature_str);

        $params = array(
            'MrchLogin' => $this->_login,
            'OutSum' => $this->_sum_of_order,
            'InvId' => $order_id,
            'Desc' => $this->_order_desc,
            'SignatureValue' => $signature_value,
        );
        
        $params = array_merge($params, $this->_user_params);

        $url = $this->_payment_base_url . '?' . http_build_query($params);

        return $url;
    }

    protected function _getXmlContent($method, $params)
    {
        $request_url = $this->_xml_base_url . $method . '?' . http_build_query($params);

        $content = file_get_contents($request_url);

        return $content;
    }

    public function getPaymentSystemStatus()
    {
        $order_id = $this->getOrderId();

        $signature = md5($this->_login . ':' . $order_id . ':' . $this->_pass2);

        $params = array(
            'MerchantLogin' => $this->_login,
            'InvoiceID' => $order_id,
            'Signature' => $signature,
        );

        $content = $this->_getXmlContent('OpState', $params);

        $xml = simplexml_load_string($content);
        
        if ((int)$xml->Result->Code == 0)
        {
            return (int)$xml->State->Code;
        } 
        else
        {
            return 0;
        }
    }

    public function getPaymentSystemStatusMsg($status)
    {
        if (isset($this->_rabokassa_status_msg[$status]))
        {
            return $this->_rabokassa_status_msg[$status];
        } 
        else
        {
            return 'Неизвестный статус [' . $status . ']';
        }
    }

    public function isResultUrlParamsCorrect()
    {
        if ($this->_result_url_method == 'GET')
        {
            $check_array = &$_GET;
        } 
        else
        {
            $check_array = &$_POST;
        }
        
        if (isset($check_array['OutSum']) && isset($check_array['InvId']) && isset($check_array['SignatureValue']))
        {
            $out_sum = (string) $check_array['OutSum'];
            $inv_id = (string) $check_array['InvId'];
            $signature_value = (string) $check_array['SignatureValue'];
            
            $signature_str = $out_sum . ':' . $inv_id . ':' . $this->_pass2;
            
            $user_params = array();
            foreach ($check_array as $k=>$v)
            {
                if (strtoupper(substr($k, 0, 3)) == 'SHP')
                {
                    $user_params[$k] = (string)$v;
                }                
            }
            
            ksort($user_params);
            
            foreach ($user_params as $k=>$v)
            {
                $signature_str .= ':' . $k . '=' . $v;
            }           
            
            if ($signature_value == strtoupper(md5($signature_str)))
            {
                return true;
            }
        }
        return false;
    }

    public function isSuccessUrlParamsCorrect()
    {
        if ($this->_success_url_method == 'GET')
        {
            $check_array = &$_GET;
        }
        else
        {
            $check_array = &$_POST;
        }

        if (isset($check_array['OutSum']) &&
            isset($check_array['InvId']) &&
            isset($check_array['SignatureValue']) &&
            isset($check_array['Culture']))
        {
            $out_sum = (string) $check_array['OutSum'];
            $inv_id = (string) $check_array['InvId'];
            $signature_value = (string) $check_array['SignatureValue'];

            $signature_str = $out_sum . ':' . $inv_id . ':' . $this->_pass1;
            
            $user_params = array();
            foreach ($check_array as $k=>$v)
            {
                if (strtoupper(substr($k, 0, 3)) == 'SHP')
                {
                    $user_params[$k] = (string)$v;
                }                
            }
            
            ksort($user_params);
            
            foreach ($user_params as $k=>$v)
            {
                $signature_str .= ':' . $k . '=' . $v;
            }   
            
            if ($signature_value == md5($signature_str))
            {
                return true;
            }
        }
        return false;
    }

    public function getResultUrlParams()
    {
        if ($this->_result_url_method == 'GET')
        {
            return $_GET;
        } 
        else
        {
            return $_POST;
        }
    }

    public function getSuccessUrlParams()
    {
        if ($this->_success_url_method == 'GET')
        {
            return $_GET;
        }
        else
        {
            return $_POST;
        }
    }

    public function getFailUrlParams()
    {
        if ($this->_fail_url_method == 'GET')
        {
            return $_GET;
        } 
        else
        {
            return $_POST;
        }
    }
    
    public function setUserParams($params)
    {
        ksort($params);        
        $this->_user_params = $params;
        return $this;
    }
    
    public function addUserParams($params)
    {
        $this->_user_params = array_merge($this->_user_params, $params);
        ksort($this->_user_params);
        return $this;
    }

}