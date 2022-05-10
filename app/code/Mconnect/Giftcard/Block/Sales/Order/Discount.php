<?php
/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Mconnect\Giftcard\Block\Sales\Order;

use Magento\Sales\Model\Order;

class Discount extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    public function getSource()
    {
        return $this->_source;
    }
 
    public function displayFullSummary()
    {
        return true;
    }
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        
        $giftcode = $this->_order->getGiftcode();
        $title = 'Gift Code Discount ('.$giftcode.')';
        $store = $this->getStore();
        
        if ($this->_order->getGiftcodeDiscount()!=0) {
            $customAmount = new \Magento\Framework\DataObject(
                [
                        'code' => 'giftcodediscount',
                        'strong' => false,
                        'value' => $this->_order->getGiftcodeDiscount(),
                        'label' => __($title),
                    ]
            );
            $parent->addTotal($customAmount, 'giftcodediscount');
        }
        return $this;
    }
    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
