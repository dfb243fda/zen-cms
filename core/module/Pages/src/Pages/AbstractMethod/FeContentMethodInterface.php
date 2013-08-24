<?php

namespace Pages\AbstractMetod;

use App\Method\MethodInterface;

interface FeContentMethodInterface extends MethodInterface 
{
    public function setContentData($contentData);
}