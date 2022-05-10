<?php

namespace Mconnect\Giftcard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
	protected $_helper;
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
	 
	public function __construct(
		\Mconnect\Giftcard\Helper\Data $helper
    ) {
		$this->_helper = $helper;
    }
	
    public function execute(EventObserver $observer)
    {
        $this->quote = $observer->getQuote();
        $this->order = $observer->getOrder();
        
        $giftcode           = $this->quote->getGiftcode();
        $giftcodeDiscount   = $this->quote->getGiftcodeDiscount();
        
        $this->order->setGiftcode($giftcode);
        $this->order->setGiftcodeDiscount($giftcodeDiscount);
        
        if ($giftcode != '') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
            $coupon->load($giftcode, 'giftcode');
            $current_balance = $coupon->getCurrentBalance();
            $current_balance = $current_balance + $giftcodeDiscount;
            $coupon->setCurrentBalance($current_balance);
            $coupon->save();
        }
        
        // can not find a equivalent event for sales_convert_quote_item_to_order_item
        foreach ($this->order->getItems() as $orderItem) {
            // @codingStandardsIgnoreLine
            if (!$orderItem->getParentItemId() &&
                $orderItem->getProductType() == \Mconnect\Giftcard\Model\Product\Type\Giftcard::TYPE_CODE) {
                if ($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId())) {
                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        if ($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')) {
                            $additionalOptions = array_merge($additionalOptionsQuote, $additionalOptionsOrder);
                        } else {
                            $additionalOptions = $additionalOptionsQuote;
                        }
                        
                        if  (is_countable($additionalOptions) && count($additionalOptions) > 0) {
                            $options = $orderItem->getProductOptions();
							
							
                            $options['additional_options'] = $this->_helper->getUnserializeData($additionalOptions->getValue());
							
							
                            $orderItem->setProductOptions($options);
                        }
                    }
                                        
                    if ($gc_image_path = $orderItem->getOptionByCode('gc_image_path')) {
                        $options = $orderItem->getProductOptions();
                        $options['gc_image_path'] = $this->_helper->getUnserializeData($gc_image_path->getValue());
                        $orderItem->setProductOptions($options);
                    }
                    
                    if ($gc_custom_image = $orderItem->getOptionByCode('gc_custom_image')) {
                        $options = $orderItem->getProductOptions();
                        $options['gc_custom_image'] = $this->_helper->getUnserializeData($gc_custom_image->getValue());
                        $orderItem->setProductOptions($options);
                    }
                }
            }
        }
    }
    private function getQuoteItemById($id)
    {
        if (empty($this->quoteItems)) {
        // @codingStandardsIgnoreLine
        /* @var  \Magento\Quote\Model\Quote\Item $item */
            foreach ($this->quote->getItems() as $item) {
                // @codingStandardsIgnoreLine
                if (!$item->getParentItemId() &&
                    $item->getProductType() == \Mconnect\Giftcard\Model\Product\Type\Giftcard::TYPE_CODE) {
                    $this->quoteItems[$item->getId()] = $item;
                }
            }
        }
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }
        return null;
    }
}
