<?php

namespace App\Log\Writer;

use Traversable;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Exception;
use Zend\Log\Formatter;
use Zend\Log\Formatter\Db as DbFormatter;

class Db extends \Zend\Log\Writer\Db
{
    public function __construct($db, $tableName = null, array $columnMap = null, $separator = null)
    {
        parent::__construct($db, $tableName, $columnMap, $separator);

        $this->formatter->setDateTimeFormat('U');
    }
}