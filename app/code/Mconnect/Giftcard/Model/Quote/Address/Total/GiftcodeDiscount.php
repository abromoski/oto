<?php
namespace Mconnect\Giftcard\Model\Quote\Address\Total;

class GiftcodeDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $couponFactory;
    protected $messageManager;
    
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Mconnect\Giftcard\Model\GiftcodeFactory $couponFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
    
        $this->setCode('giftcodediscount');
        $this->couponFactory = $couponFactory;
        $this->messageManager = $messageManager;
        
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }
 
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
    
        parent::collect($quote, $shippingAssignment, $total);
        
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        
        $giftcode = $quote->getGiftcode();
        $itemsCount = $quote->getItemsCount();
        
        if ($giftcode != null && $itemsCount > 0) {
            $coupon = $this->couponFactory->create();
            $coupon->load($giftcode, 'giftcode');
            
            $isCodeValid        = false;
            $validate_product   = false;
            $validate_expiry    = false;
            $validate_for_self  = false;
            $validate_status    = false;
            $validate_balance   = false;
            $validate_with_coupon = false;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $escaper = $objectManager->get('Magento\Framework\Escaper');
            $helper = $objectManager->create('\Mconnect\Giftcard\Helper\Data');
            
            if ($coupon->getId()) {
                $isCodeValid = true;
            } else {
                $this->messageManager->addError(
                    __(
                        'The gift code "%1" is not valid.',
                        $escaper->escapeHtml($giftcode)
                    )
                );
            }
            //=================================
            $status = $coupon->getStatus();
            
            if ($status == \Mconnect\Giftcard\Model\Giftcode::STATUS_ACTIVE ||
                $status == \Mconnect\Giftcard\Model\Giftcode::STATUS_PARTIALUSED
            ) {
                $validate_status = true;
            } else {
                $staus_labels = $coupon->getAvailableStatuses();
                $staus_label = $staus_labels[$status];
                
                $this->messageManager->addError(
                    __(
                        'The gift code "%1" is '.$staus_label.'.',
                        $escaper->escapeHtml($giftcode)
                    )
                );
            }
            //=================================
            $current_balance = $coupon->getCurrentBalance();
            if ($current_balance > 0) {
                $validate_balance   = true;
            } else {
                $this->messageManager->addError(
                    __(
                        'The gift code "%1" has no sufficient balance.',
                        $escaper->escapeHtml($giftcode)
                    )
                );
            }
            //=================================
            $product_types = $helper->getAllowedProductTypes();
            $product_types  = explode(",", $product_types);
            
            foreach ($quote->getAllVisibleItems() as $item) {
                $item_type = $item->getProduct()->getTypeId();
                if (in_array($item_type, $product_types)) {
                    $validate_product = true;
                    break;
                }
            }
            
            if (!$validate_product) {
                $this->messageManager->addError(
                    __(
                        'The gift code "%1" is not valid for currently exists product(s) in cart.',
                        $escaper->escapeHtml($giftcode)
                    )
                );
            }
            //=================================
            $expiry_date = $coupon->getExpiryDate();
            if (strtotime($expiry_date) > time()) {
                $validate_expiry = true;
            } else {
                $this->messageManager->addError(
                    __(
                        'The gift code "%1" is expired.',
                        $escaper->escapeHtml($giftcode)
                    )
                );
            }
            //=================================
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if ($customerSession->isLoggedIn()) {
                $sender_id      = $coupon->getSenderId();
                $sender_email   = $coupon->getSenderEmail();
                
                $cust_id    = $customerSession->getCustomer()->getId();
                $cust_email     = $customerSession->getCustomer()->getEmail();
                
                if ($sender_id != $cust_id && $sender_id != null) {
                    $validate_for_self  = true;
                } elseif ($sender_id == $cust_id && $sender_id != null) {
                    if ($helper->getUseForSelf()) {
                        $validate_for_self  = true;
                    } else {
                        $this->messageManager->addError(
                            __(
                                'Please be aware that it is not possible to use the gift card you purchased for your own orders. (Gift code "%1" )',
                                $escaper->escapeHtml($giftcode)
                            )
                        );
                    }
                } elseif ($sender_id == null) {
                    $validate_for_self  = true;
                }
                
                //Please be aware that it is not possible to use the gift card you purchased for your own orders.
            } else {
                $validate_for_self  = true;
            }
            //=================================
            $use_with_coupon = $helper->getUseWithCouponCodes();
            $appliedCartDiscount = 0;
                    
            if ($quote->getCouponCode()) {
                // If a discount exists in cart and another discount is applied, the add both discounts.
                $appliedCartDiscount = $total->getDiscountAmount();
                $discountAmount      = $total->getDiscountAmount();
                $label               = $total->getDiscountDescription();
             
                if ($use_with_coupon) {
                    $validate_with_coupon = true;
                } else {
                    $quote->setGiftcode('');
                    $quote->setGiftcodeDiscount('0.0000');
                    $this->messageManager->addError(
                        __(
                            'You can not apply gidt code with coupon code.'
                        )
                    );
                }
            } else {
                $validate_with_coupon = true;
            }
            
            //=================================
            if ($isCodeValid && $validate_product && $validate_expiry && $validate_for_self && $validate_with_coupon) {
                //=============================================
                $amount = 0;
                foreach ($quote->getAllVisibleItems() as $item) {
                    $item_price = $item->getPrice();
                    $item_type = $item->getProductType();
                    $item_qty = $item->getQty();
                    
                    if (in_array($item_type, $product_types)) {
                        $amount = $amount + $item_price * $item_qty;
                    }
                }
                //=============================================
                
                if ($current_balance < $amount) {
                    $amount = $current_balance;
                }
                
                if ($amount > 0) {
                    $address = $shippingAssignment->getShipping()->getAddress();
                    
                    $st_wd  = $total->getSubtotalWithDiscount();
                    $bst_wd     = $total->getBaseSubtotalWithDiscount();
                    
                    if ($appliedCartDiscount != 0) {
                        if ($st_wd < $amount) {
                            $amount = $st_wd;
                        }
                    }
                    
                    $giftcodeDiscount = -$amount;
                    $total->addTotalAmount('giftcodediscount', $giftcodeDiscount);
                    $total->addBaseTotalAmount('giftcodediscount', $giftcodeDiscount);
                    $total->setSubtotalWithDiscount($total->getSubtotalWithDiscount() + $giftcodeDiscount);
                    $total->setBaseSubtotalWithDiscount($total->getBaseSubtotalWithDiscount() + $giftcodeDiscount);
                    
                    $quote->setGiftcodeDiscount($giftcodeDiscount);
                }
            }
        }
        return $this;
    }
 
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getGiftcodeDiscount();
 
        if ($amount != 0) {
            $result = [
                'code' => 'giftcodediscount',
                'title' => __('Gift Code Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }
}
