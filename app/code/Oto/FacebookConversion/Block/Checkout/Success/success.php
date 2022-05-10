<?php
// @codingStandardsIgnoreFile

namespace Oto\FacebookConversion\Block\Checkout\Success;

/**
 * FB Page Block
 */
class Facebookpixel extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Sales\Model\OrderFactory */
    protected $_salesFactory;

    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_salesFactory = $salesOrderFactory;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }


    /**
     * Retrieve current order
     *
     * @return \Magento\Sales\Model\Order\OrderFactory
     */
    public function getOrder()
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        return $this->_salesFactory->load($orderId);
    }
}