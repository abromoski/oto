<?php

namespace Mconnect\Giftcard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    
    private $_giftcodeFactory;
    private $_giftcodeCollectionFactory;
    private $_quoteItemFactory;
    protected $_itemCollectionFactory;
    protected $messageManager;
    protected $customerRepository;
    protected $_extensibleDataObjectConverter;
    
    public function __construct(
        \Mconnect\Giftcard\Model\GiftcodeFactory $giftcodeFactory,
        \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory $giftcodeCollectionFactory,
        \Magento\Quote\Model\Quote\Item $quoteItemFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
    
        $this->_giftcodeFactory = $giftcodeFactory;
        $this->_giftcodeCollectionFactory = $giftcodeCollectionFactory;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->messageManager = $messageManager;
        $this->customerRepository = $customerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }
    
    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $this->order = $observer->getEvent()->getOrder();
        
        foreach ($this->order->getAllItems() as $orderItem) {
            if ($orderItem->getProductType() == \Mconnect\Giftcard\Model\Product\Type\Giftcard::TYPE_CODE) {
                $itemStatusId = $orderItem->getStatusId();
                
                if ($itemStatusId == \Magento\Sales\Model\Order\Item::STATUS_INVOICED ||
                    $itemStatusId == \Magento\Sales\Model\Order\Item::STATUS_SHIPPED) {
                    $quoteItemId = $orderItem->getQuoteItemId();
                    
                    if (!$this->isGiftcodeGenerated($this->order->getId(), $quoteItemId)) {
                        $this->assignGiftcode($this->order, $orderItem);
                    }
                }

                if ($itemStatusId == \Magento\Sales\Model\Order\Item::STATUS_REFUNDED) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $helper = $objectManager->get('Mconnect\Giftcard\Helper\Data');
                    
                    if ($helper->getNotifyRecipientRefunds()) {
                        $quoteItemId = $orderItem->getQuoteItemId();
                        $this->notifyRefundsToRecipient($quoteItemId);
                    }
                }
            }
        }
    }
    
    public function isGiftcodeGenerated($orderId, $quoteItemId)
    {
        
        if ($orderId != '' && $quoteItemId != '') {
            $gccollection = $this->_giftcodeCollectionFactory->create();
            $gccollection->addFieldToFilter('order_id', ['eq' => $orderId]);
            $gccollection->addFieldToFilter('quote_item_id', ['eq' => $quoteItemId]);
            
            if (count($gccollection)) {
                return true;
            }
        }
        return false;
    }
    
    public function assignGiftcode($order, $orderItem)
    {
        
        $quoteItemId    = $orderItem->getQuoteItemId();
        $product        = $orderItem->getProduct();
        $code_pattern   = $product->getGcCodePattern();
        $gc_lifetime    = $product->getGcLifetime();
        $card_type      = $product->getGcCardType();
        $expiry_date    = date('Y-m-d', strtotime("+".$gc_lifetime." days"));
        
        $qty = $orderItem->getQtyInvoiced();
        
        for ($i=0; $i<$qty; $i++) {
            do {
                $giftcode = $this->generateGiftcode($code_pattern);
            } while ($this->isDuplicateCode($giftcode));
            
            // Save giftcode
            $product_options    = $orderItem->getProductOptions();
            $options    = $product_options['info_buyRequest'];
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->get('Mconnect\Giftcard\Helper\Data');
            
            //$quote  = $objectManager->create('\Magento\Quote\Model\Quote')->load($this->order->getQuoteId());
            $quote  = $objectManager->create('\Magento\Quote\Model\Quote')->loadByIdWithoutStore($this->order->getQuoteId());
            
            $quoteItemCollection = $this->_itemCollectionFactory->create()
                                    ->setQuote($quote)
                                    ->addFieldToFilter('item_id', $quoteItemId);
            
            $quoteItem = $quoteItemCollection->getFirstItem();
            
			
            $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $store = $storeManager->getStore($quoteItem->getStoreId());
            $websiteId = $store->getWebsiteId();
            try {
                $recepient = $this->customerRepository->get($options['gc_recipient_email'], $websiteId);
            } catch (\Exception $e) {
                /** If recepient does not exist do nothing. */
            }
            
            $cust_data = isset($recepient) ? $this->_extensibleDataObjectConverter->toFlatArray($recepient, [], '\Magento\Customer\Api\Data\CustomerInterface') : [];
            
            $image_path = ($quoteItem->getOptionByCode('gc_image_path') != null)
                ? $quoteItem->getOptionByCode('gc_image_path')->getValue() : '';
                
            $custom_image_path  = ($quoteItem->getOptionByCode('gc_custom_image') != null)
                ? $quoteItem->getOptionByCode('gc_custom_image')->getValue() : '';
                
            $email_template     = ($quoteItem->getOptionByCode('gc_template') != null)
                ? $quoteItem->getOptionByCode('gc_template')->getValue() : '';
                
            $headline   = ($quoteItem->getOptionByCode('headline') != null) ? $quoteItem->getOptionByCode('headline')->getValue() : '';
            
            $gc_data = [];
            $gc_data['giftcode']    = $giftcode;
            $gc_data['product_id']  = $product->getId();
            $gc_data['status']      = \Mconnect\Giftcard\Model\Giftcode::STATUS_ACTIVE;
            $gc_data['initial_value']   = $quoteItem->getOptionByCode('gc_amount')->getValue();
            $gc_data['current_balance'] = $quoteItem->getOptionByCode('gc_amount')->getValue();
            $gc_data['expiry_date']     = $expiry_date;
            $gc_data['email_template']  = $email_template;
            $gc_data['image_path']      = $image_path;
            $gc_data['custom_image_path'] = $custom_image_path;
            $gc_data['headline']        = $headline;
            $gc_data['comment']         = $options['gc_message'];
            $gc_data['order_id']        = $order->getId();
            $gc_data['increment_id']    = $order->getIncrementId();
            $gc_data['quote_item_id']   = $quoteItemId;
            $gc_data['sender_id']       = $order->getCustomerId();
            $gc_data['sender_email']    = $options['gc_sender_email'];
            $gc_data['sender_name']     = $options['gc_sender_name'];
            
            if (isset($cust_data['id'])) {
                $gc_data['recipient_id']    = $cust_data['id'];
            }
            
            $gc_data['recipient_name']  = $options['gc_recipient_name'];
            $gc_data['recipient_email'] = $options['gc_recipient_email'];
            $gc_data['store_id']        = $quoteItem->getStoreId();
            
            $gc_model = $this->_giftcodeFactory->create();
            $gc_model->setData($gc_data);
            
            try {
                $gc_model->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                    ->addException($e, __($e->getMessage().'Something went wrong while saving the page.'));
            }
            
            if ($gc_model->getGiftcodeId()) {
                //send giftcard email
                $flag_send = 0;
                if (isset($options['day_to_send'])) {
                    $time_zone  = $options['time_zone'];
                    $tz_date    = new \DateTime();
                    $tz_date->setTimezone(new \DateTimeZone($time_zone));
                    $today      = new \DateTime($tz_date->format('Y-m-d'));
                    $send_day   = new \DateTime(date("Y-m-d", strtotime($options['day_to_send'])));
                    
                    if (($today >= $send_day || (!isset($options['day_to_send']) || $options['day_to_send'] == ''))
                        && $card_type != \Mconnect\Giftcard\Model\Product\Attribute\Source\CardType::PHYSICAL
                    ) {
                        /* Receiver Detail  */
                        $receiverInfo = [
                            'name' => $gc_data['recipient_name'],
                            'email' => $gc_data['recipient_email']
                        ];
                         
                         
                        /* Sender Detail  */
                        $senderInfo = $helper->getEmailSender();
                         
                        $_gc_email = $objectManager->create(
                            'Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email'
                        );
                        
                        $email_template     = $_gc_email->loadEmailDataById($gc_data['email_template']);
        
                        $templateId     = $email_template[0]['email_template'];
                        $template_image     = $email_template[0]['image_path'];
        
                        $mediaurl = $storeManager->getStore()
                            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                        $mediaurl = $mediaurl."catalog/product";
                        
                        if ($custom_image_path != "") {
                            $template_image = $mediaurl.'/customgiftcard'.$custom_image_path;
                        } else {
                            $template_image = $mediaurl.$template_image;
                        }
                        
                        $pricingHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
                        
                        /* Assign values for your template variables  */
                        $vars = [];
                        $vars['sender_name']    = $gc_data['sender_name'];
                        $vars['recipient_name'] = $gc_data['recipient_name'];
                        $vars['balance']        = $pricingHelper->currency($gc_data['current_balance'], true, false);
                        $vars['card_image_base_url'] = $template_image;
                        $vars['headline']       = $gc_data['headline'];
                        $vars['message']        = $gc_data['comment'];
                        $vars['giftcode']       = $gc_model->getGiftcode();
         
                        /* call send mail method from helper or where you define it*/

                        try {
                            $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                                $vars,
                                $senderInfo,
                                $receiverInfo,
                                $templateId
                            );
                            $send_copy = $helper->getSendCopyTo();
                            if ($send_copy != '') {
                                $copy_emails = explode(",", $send_copy);
                                
                                foreach ($copy_emails as $copy_email) {
                                    if ($copy_email != '') {
                                        $receiverInfo['email'] = $copy_email;
                                        $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                                            $vars,
                                            $senderInfo,
                                            $receiverInfo,
                                            $templateId
                                        );
                                    }
                                }
                            }
                            
                            if ($helper->getConfirmToSender()) {
                                $templateId = $helper->getSenderConfirmationTemplate();
                                $receiverInfo = [
                                    'name' => $gc_data['sender_name'] ,
                                    'email' => $gc_data['sender_email'],
                                ];
                                
                                $vars = [];
                                $vars['sender_name']    = $gc_data['sender_name'];
                                $vars['recipient_name'] = $gc_data['recipient_name'];
                                
                                $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                                    $vars,
                                    $senderInfo,
                                    $receiverInfo,
                                    $templateId
                                );
                            }
                            
                            $gc_model->setIsMailSent('1');
                            $gc_model->save();
                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $this->messageManager->addError($e->getMessage());
                        } catch (\RuntimeException $e) {
                            $this->messageManager->addError($e->getMessage());
                        } catch (\Exception $e) {
                            $this->messageManager
                            ->addException($e, __($e->getMessage().'Something went wrong while saving the page.'));
                        }
                    }
                }
            }
        }
    }
    
    public function generateGiftcode($pattern)
    {
        $nums   = "0123456789";
        $chars  = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        
        $slug_arr = preg_split('/( }{|}|{| )/', $pattern);
        
        $res = "";
        foreach ($slug_arr as $slug) {
            $slug_val = $slug;
            
            if ($slug == 'L' || $slug == 'l') {
                $slug_val = $nums[mt_rand(0, strlen($nums)-1)];
            }
            if ($slug == 'D' || $slug == 'd') {
                $slug_val = $chars[mt_rand(0, strlen($chars)-1)];
            }
            $res .= $slug_val;
        }
        return $res;
    }
    
    public function isDuplicateCode($giftcode)
    {
        $gccollection = $this->_giftcodeCollectionFactory->create();
        $gccollection->addFieldToFilter('giftcode', ['eq' => $giftcode]);
        
        if (count($gccollection)) {
            return true;
        }
        return false;
    }
    
    public function notifyRefundsToRecipient($quoteItemId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Mconnect\Giftcard\Helper\Data');
        
        $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
        $coupon->load($quoteItemId, 'quote_item_id');
        $product_id = $coupon->getProductId();
        
        $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($product_id);
        $card_type = $product->getGcCardType();
        
        try {
            $templateId = $helper->getNotifyRecipientRefundsTemplate();
            $senderInfo = $helper->getEmailSender();
            $receiverInfo = [
                    'name' => $coupon->getRecipientName(),
                    'email' => $coupon->getRecipientEmail()
                ];
            
            $vars = [];
            $vars['sender_name']    = $coupon->getSenderName();
            $vars['recipient_name'] = $coupon->getRecipientName();
            $vars['giftcode']       = $coupon->getGiftcode();
            
            if ($card_type != \Mconnect\Giftcard\Model\Product\Attribute\Source\CardType::PHYSICAL) {
                $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                    $vars,
                    $senderInfo,
                    $receiverInfo,
                    $templateId
                );
            }
            
            $coupon->setStatus(\Mconnect\Giftcard\Model\Giftcode::STATUS_INACTIVE);
            $coupon->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage().'Something went wrong while saving the page.'));
        }
    }
}
