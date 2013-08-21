<?php

namespace Pages\Entity;

use App\Method\AbstractMethod;

abstract class FePageMethod extends AbstractMethod implements FePageMethodInterface
{
    protected $pageData;
    
    public function setPageData($pageData)
    {
        $this->pageData = $pageData;
    }
}