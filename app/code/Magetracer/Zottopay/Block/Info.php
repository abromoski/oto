<?php
/**
 * Copyright © 2016 Magetracer. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetracer\Zottopay\Block;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_payableTo;

    /**
     * @var string
     */
    protected $_mailingAddress;

    /**
     * @var string
     */
    protected $_template = 'Magetracer_Zottopay::info.phtml';

    
    public function getMethodCode()
    {
        return $this->getInfo()->getMethodInstance()->getCode();
    }

    
}
