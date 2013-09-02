<?php

namespace App\View\ResultComposer;

interface ComposerInterface
{
    public function setTarget($target);
    
    public function getResult(array $resultArray);
}