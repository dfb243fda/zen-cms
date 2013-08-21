<?php

namespace Elfinder\Method;

use App\Method\MethodInterface;

class Files implements MethodInterface
{    
    public function init() {}
    
    public function main()
    {
        return array(
            'contentTemplate' => array(
                'name' => 'content_template/Elfinder/files.phtml',
                'data' => array(),
            ),
        );
    }
}