<?php

namespace Brandbassador\Plugin\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\View\Element\Template;

class Layout extends Template
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function toHtml()
    {
        return parent::toHtml();
    }
}