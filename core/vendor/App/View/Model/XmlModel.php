<?php

namespace App\View\Model;

use Zend\View\Model\ViewModel;

class XmlModel extends ViewModel
{
    /**
     * JSON probably won't need to be captured into a
     * a parent container by default.
     *
     * @var string
     */
    protected $captureTo = null;

    /**
     * XML is usually terminal
     *
     * @var bool
     */
    protected $terminate = true;
}
