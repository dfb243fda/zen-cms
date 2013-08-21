<?php

/**
 * Класс для совершения платежей
 */
abstract class App_Payment_Abstract
{

    /**
     * Название платежной системы, обязательно должно быть переопределено
     * @var string
     */
    protected $_payment_system; //в реализации необходимо переопределить
    protected $_payment_orders_tbl = 'APP_DISKO.PAYMENT_ORDERS';
    protected $_connection;

    /**
     * Сумма заказа
     * @var float
     */
    protected $_sum_of_order;

    /**
     * Id пользователя
     * @var string
     */
    protected $_user_id;

    /**
     * ID заказа
     * @var int
     */
    protected $_order_id;

    /**
     * Валюта, в которой совершается платеж
     * @var string
     */
    protected $_currency;

    /**
     * Описание заказа
     * @var string
     */
    protected $_order_desc;

    /**
     * Язык интерфейса оплаты
     * @var string
     */
    protected $_language;

    /**
     * Режим тестирования
     * @var boolean
     */
    protected $_test_mode = true;

    /**
     * Статус заказа
     * @var string 
     */
    protected $_order_status;

    /**
     * Время жизни резервирования заказа
     * @var integer 
     */
    protected $_reserve_timeout = 300;

    /**
     * Статус заказа: зарезервировано 
     */

    const STATUS_RESERVED = 'R';
    /**
     * Статус заказа: оплачено 
     */
    const STATUS_PAID = 'P';
    /**
     * Статус заказа: отменено 
     */
    const STATUS_CANCELLED = 'C';

    protected $_order_status_msg = array(
        self::STATUS_RESERVED => 'Заказ зарезервирован на оплату',
        self::STATUS_PAID => 'Оплата успешно проведена',
        self::STATUS_CANCELLED => 'Оплата отменена пользователем',
    );

    public function __construct($options = array())
    {
        if (!isset($options['connection']))
        {
            $this->setConnection(Yii::app()->oci->connect());
        }
        if (!isset($options['userId']))
        {
            if (Yii::app()->user->isGuest)
            {
                $this->setUserId('');
            }
            else
            {
                $this->setUserId(Yii::app()->user->username);
            }            
        }
        if (!isset($options['sumOfOrder']))
        {
            throw new App_Payment_Exception('The sum option is required');
        }
        if (!isset($options['currency']))
        {
            throw new App_Payment_Exception('The currency option is required');
        }
        if ($this->_payment_system === null)
        {
            throw new App_Payment_Exception('The _payment_system property is required');
        }

        $this->setOptions($options);
    }

    /**
     * Устанавливает опции во внутренние поля класса
     * @param array $options
     * @throws Payment_Exception 
     */
    public function setOptions($options)
    {
        if (!is_array($options))
        {
            throw new App_Payment_Exception('setOptions() expects either an array');
        }

        foreach ($options as $key => $value)
        {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method))
            {
                $this->$method($value);
            }
        }
    }

    public function setConnection($conn)
    {
        $this->_connection = $conn;
        return $this;
    }

    public function setSumOfOrder($sum)
    {
        $this->_sum_of_order = $sum;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->_user_id = $user_id;
        return $this;
    }

    public function setCurrency($currency)
    {
        $this->_currency = $currency;
        return $this;
    }

    public function setOrderId($order_id)
    {
        $this->_order_id = $order_id;
        return $this;
    }

    protected function _setOrderStatus($order_status)
    {
        $this->_order_status = $order_status;
        return $this;
    }

    public function setOrderDesc($order_desc)
    {
        $this->_order_desc = $order_desc;
        return $this;
    }

    public function setTestMode($test_mode)
    {
        $this->_test_mode = $test_mode;
        return $this;
    }

    public function setLanguage($language)
    {
        $this->_language = $language;
        return $this;
    }

    public function setReserveTimeOut($sec)
    {
        $this->_reserve_timeout = $sec;
        return $this;
    }

    public function getOrderId()
    {
        return $this->_order_id;
    }

    public function getOrderStatus()
    {
        return $this->_order_status;
    }

    public function getOrderStatusMsg()
    {
        $status = $this->getOrderStatus();
        if (isset($this->_order_status_msg[$status]))
        {
            return $this->_order_status_msg[$status];
        } 
        else
        {
            return 'Неизвестный статус [' . $status . ']';
        }
    }

    public function reserveOrder()
    {
        $db = Zend_Registry::get('db'); 
        
        $status = self::STATUS_RESERVED;
        $user_ip = Yii::app()->getRequest()->getUserHostAddress();
        
        try
        {
            $db->insert($this->_payment_orders_tbl, array(
                'SUM' => $this->_sum_of_order,
                'USER_ID' => $this->_user_id,
                'ORDER_DATE' => new Zend_Db_Expr('current_timestamp'),
                'STATUS' => $status,
                'USER_IP' => $user_ip,
                'PAYMENT_SYSTEM' => $this->_payment_system,
                'CURRENCY' => $this->_currency,
                'RESERVE_TIMEOUT' => $this->_reserve_timeout,
            ));

            
            $conn = Yii::app()->oci->connect();

            $query = "SELECT APP_DISKO.PAYMENT_ORDERS_SEQ.CURRVAL AS scurrent FROM dual";

            $s = oci_parse($conn, $query);

            oci_execute($s);

            $row = oci_fetch_array($s);

            $order_id = $row['SCURRENT'];         

            //      $order_id = $db->lastInsertId('APP_DISKO.PAYMENT_ORDERS');

            $this->setOrderId($order_id)->_setOrderStatus(self::STATUS_RESERVED);
        }
        catch (Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }       

        return $this;
    }

    protected function _setOrderStatusToDb($status)
    {
        $db = Zend_Registry::get('db');
        
        $user_ip = Yii::app()->getRequest()->getUserHostAddress();
        
        try
        {
             $db->update($this->_payment_orders_tbl, array(
                'STATUS' => $status,
                'USER_IP' => $user_ip,
            ), 'ID = ' . $this->_order_id);
        }
        catch (Exception $e)
        {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return false;
        }              

        return true;
    }

    public function setPaidStatus()
    {
        $result = array();

        $db = Zend_Registry::get('db');       
        
        $sql_result = $db->query("SELECT STATUS, (cast(current_timestamp as date) - cast(ORDER_DATE as date))*24*60*60 AS TIMEOUT, RESERVE_TIMEOUT 
            from ". $this->_payment_orders_tbl ."
            WHERE \"ID\"='".$this->_order_id."'")
            ->fetchAll();
      
       /* 
        $sql_result = $db->select()
            ->from($this->_payment_orders_tbl, array(
                'status',
                new Zend_Db_Expr('(cast(current_timestamp as date) - cast(ORDER_DATE as date))*24*60*60 AS timeout')
            ), 'APP_DISKO')
            ->where('id = ?', $this->_order_id)
            ->query()
            ->fetchAll();        
         */
        if (empty($sql_result))
        {
            $result['success'] = false;
            $result['error_msg'] = 'Заказ не найден.';
        }
        else
        {
            $row = $sql_result[0];
            
            if ($row['STATUS'] == self::STATUS_RESERVED)
            {
                if ($row['TIMEOUT'] > $row['RESERVE_TIMEOUT'])
                {
                    $result['success'] = false;
                    $result['error_msg'] = 'Время заказа истекло';
                } 
                else
                {
                    $result['success'] = $this->_setOrderStatusToDb(self::STATUS_PAID);
                    if ($result['success'] == false)
                    {
                        $result['error_msg'] = 'Произошла ошибка при изменении статуса заказа на "оплачен"';
                    }
                }
            } 
            elseif ($row['STATUS'] == self::STATUS_PAID)
            {
                $result['success'] = false;
                $result['error_msg'] = 'Заказ уже оплачен.';
            } 
            elseif ($row['STATUS'] == self::STATUS_CANCELLED)
            {
                $result['success'] = false;
                $result['error_msg'] = 'Заказ отменён.';
            } 
            else
            {
                $result['success'] = false;
                $result['error_msg'] = 'Неизвестный статус заказа [' . $row['status'] . ']';
            }
        }
       
        return $result;
    }

    public function setCancelledStatus()
    {
        $result = array();

        $sql = "select 1 from " . $this->_payment_orders_tbl . "
	    where id = :id";

        $s = oci_parse($this->_connection, $sql);

        oci_bind_by_name($s, ':id', $this->_order_id);

        oci_execute($s);

        if ($row = oci_fetch_assoc($s))
        {
            $result['success'] = $this->_setOrderStatusToDb(self::STATUS_PAID);
            if ($result['success'] == false)
            {
                $result['error_msg'] = 'Произошла ошибка при изменении статуса заказа на "отменен"';
            }
        } 
        else
        {
            $result['success'] = false;
            $result['error_msg'] = 'Заказ не найден';
        }

        return $result;
    }

    abstract public function getPaymentSystemStatus();
}