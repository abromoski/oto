<?php

/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetracer\Zottopay\Block\Payment;


class Redirect extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    protected $_paymentMethod;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetracer\Zottopay\Model\PaymentMethod $paymentMethod,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_paymentMethod = $paymentMethod;
    }



    public function getCheckoutForm(){

        $parameter = $this->_paymentMethod->getCheckoutParameter();
        $gatewayUrl = $this->_paymentMethod->getGatewayUrl();

        $formHTML = '';
        $formHTML .= '<form action="'.$gatewayUrl.'" method="post" target="_top" name="payment_checkout" id="payment_checkout">';
        foreach ($parameter as $field => $value) {
            $formHTML .= '<input type="hidden" name="'.$field.'" value="'.$value.'" >';
        }
        $formHTML .= '</form>';

        return $formHTML;
    }

   

}
