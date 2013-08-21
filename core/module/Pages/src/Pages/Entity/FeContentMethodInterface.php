<?php

namespace Pages\Entity;

use App\Method\MethodInterface;

interface FeContentMethodInterface extends MethodInterface 
{
    public function setContentData($contentData);
}