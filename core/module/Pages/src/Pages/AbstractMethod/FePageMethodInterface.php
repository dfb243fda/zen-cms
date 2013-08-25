<?php

namespace Pages\AbstractMethod;

use App\Method\MethodInterface;

interface FePageMethodInterface extends MethodInterface 
{
    public function setPageData($pageData);
}