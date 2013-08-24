<?php

namespace Pages\AbstractMetod;

use App\Method\MethodInterface;

interface FePageMethodInterface extends MethodInterface 
{
    public function setPageData($pageData);
}