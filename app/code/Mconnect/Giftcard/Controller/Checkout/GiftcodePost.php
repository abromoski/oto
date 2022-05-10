<?php
namespace Mconnect\Giftcard\Controller\Checkout;

class GiftcodePost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Mconnect\Giftcard\Model\GiftcodeFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $giftcode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('giftcode'));

        $cartQuote = $this->cart->getQuote();
        $oldGiftcode = $cartQuote->getGiftcode();

        $codeLength = strlen($giftcode);
        if (!$codeLength && !strlen($oldGiftcode)) {
            return $this->_goBack();
        }

        try {
            if ($codeLength) {
                $escaper = $this->_objectManager->get('Magento\Framework\Escaper');
                $helper = $this->_objectManager->create('\Mconnect\Giftcard\Helper\Data');
                
                $isCodeValid        = false;
                $validate_product   = false;
                $validate_expiry    = false;
                $validate_for_self  = false;
                $validate_status    = false;
                $validate_balance   = false;
				$validate_customer  = false; 
                
                $coupon = $this->couponFactory->create();
                $coupon->load($giftcode, 'giftcode');
            
                //=================================
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
                    $staus_label = 'not valid';
                    if ($status != null) {
                        $status_labels = $coupon->getAvailableStatuses();
                        $staus_label = $status_labels[$status];
                    }
                    
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
                
                foreach ($cartQuote->getAllVisibleItems() as $item) {
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
                $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
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
				
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
                $currentStoreId = $storeManager->getStore()->getStoreId();
			
				$recipient_id      = $coupon->getRecipientId();
                $recipient_email   = $coupon->getRecipientEmail();
				$store_id 	   = $coupon->getStoreId();
				
				
				if(($cust_id == $recipient_id || $cust_email == $recipient_email) && $store_id == $currentStoreId)
				{
					$validate_customer  = true;
				}
				else
				{
					$this->messageManager->addError(
                        __(
                            'The gift code "%1" is not valid.',
                            $escaper->escapeHtml($giftcode)
                        )
                    );
				}
				
                //=================================
                
                $itemsCount = $cartQuote->getItemsCount();
                
                if ($itemsCount) {
                    if ($isCodeValid && $validate_product && $validate_expiry && $validate_for_self && $validate_customer) {
                        $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                        $cartQuote->setGiftcode($giftcode);
                        $this->quoteRepository->save($cartQuote);
                    }
                }
                
                if (!$itemsCount) {
                    // @codingStandardsIgnoreLine
                    if ($isCodeValid && $validate_status && $validate_balance && $validate_product &&
                        $validate_expiry && $validate_for_self && $validate_customer) {
                        $this->_checkoutSession->getQuote()->setGiftcode($giftcode)->save();
                        $this->messageManager->addSuccess(
                            __(
                                'You used gift code "%1".',
                                $escaper->escapeHtml($giftcode)
                            )
                        );
                    }
                } else {
                    // @codingStandardsIgnoreLine
                    if ($isCodeValid && $validate_status && $validate_balance && $validate_product &&
                        $validate_expiry && $validate_for_self && $validate_customer && $giftcode == $cartQuote->getGiftcode()) {
                        $this->messageManager->addSuccess(
                            __(
                                'You used gift code "%1".',
                                $escaper->escapeHtml($giftcode)
                            )
                        );
                    }
                }
            } else {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setGiftcode($giftcode);
                $cartQuote->setGiftcodeDiscount('0.0000');
                $this->quoteRepository->save($cartQuote);
                
                $this->messageManager->addSuccess(__('You canceled the gift code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the gift code.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
}
