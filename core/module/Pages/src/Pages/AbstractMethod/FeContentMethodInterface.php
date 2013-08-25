<?php

namespace Pages\AbstractMethod;

use App\Method\MethodInterface;

interface FeContentMethodInterface extends MethodInterface 
{
    public function setContentData($contentData);
}