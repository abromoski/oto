<?php

namespace Mconnect\Giftcard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitAfterObserver implements ObserverInterface
{
    private $order = null;
    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $this->order = $observer->getOrder();
        
        $giftcode           = $this->order->getGiftcode();
        $giftcodeDiscount   = $this->order->getGiftcodeDiscount();
        
        if ($giftcode != '') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
            $coupon->load($giftcode, 'giftcode');
            
            $billingAddress = $this->order->getShippingAddress();
            $shippingAddress = $this->order->getShippingAddress();
            
            $order_data = [];
            $order_data['giftcode_id']      = $coupon->getGiftcodeId();
            $order_data['order_id']             = $this->order->getOrderId();
            $order_data['increment_id']         = $this->order->getIncrementId();
            $order_data['bill_name']        = $billingAddress->getName();
            $order_data['ship_name']        = $shippingAddress->getName();
            $order_data['order_total']      = $this->order->getBaseSubtotalInclTax();
            $order_data['spent_amount']         = $giftcodeDiscount;
            $order_data['order_website']    = $this->order->getStoreId();
            
            $gift_order = $objectManager->create('\Mconnect\Giftcard\Model\Order');
            $gift_order->setData($order_data);
            $gift_order->save();
        }
    }
}
