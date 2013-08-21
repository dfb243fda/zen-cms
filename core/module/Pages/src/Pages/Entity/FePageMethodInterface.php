<?php

namespace Pages\Entity;

use App\Method\MethodInterface;

interface FePageMethodInterface extends MethodInterface 
{
    public function setPageData($pageData);
}