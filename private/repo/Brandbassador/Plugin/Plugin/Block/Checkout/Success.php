<?php

namespace Brandbassador\Plugin\Block\Checkout;

use Brandbassador\Plugin\Helper\Data;
use Magento\Sales\Model\Order;
use Brandbassador\Plugin\Logger\Logger;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\App\Http\Context as HttpContext;


class Success extends \Magento\Checkout\Block\Onepage\Success
{
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderConfig $orderConfig,
        HttpContext $httpContext,
        Order $order,
        Data $helper,
        Logger $logger
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext);
        $this->helper = $helper;
        $this->order = $order;
        $this->logger = $logger;
        $this->logger->info('initCheckoutSuccessBlock');
    }

    public function getTrackingData() {
        $this->logger->info('generateTrackingData', ['orderId' => $this->getLastOrderId()]);
        if ($this->getLastOrderId()) {
            $order = $this->order->load($this->getLastOrderId());
        } else {
            $incrementId  = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
            $order = $this->order->loadByIncrementId($incrementId);
        }
        $code = $order->getCouponCode();

        $total = floatval($order->getGrandTotal()) - floatval($order->getShippingAmount()) - floatval($order->getTaxAmount());
        $total = number_format($total, 2);

        return [
            'orderId' => $order->getIncrementId(),
            'total' => $total,
            'currency' => $order->getOrderCurrencyCode(),
            'code' => $order->getCouponCode(),
            'key' => $this->helper->getGeneralConfig('brandkey'),
            'apiURL' => $this->helper->getBrandbassadorApiUrl()
        ];
    }
}