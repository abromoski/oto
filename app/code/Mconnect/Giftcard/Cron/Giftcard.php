<?php
namespace Mconnect\Giftcard\Cron;

class Giftcard
{
 
    protected $_logger;
    protected $_giftcodeFactory;
 
    public function __construct(
        \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory $giftcodeFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_giftcodeFactory = $giftcodeFactory;
        $this->_logger = $logger;
    }
 
    public function updateStatus()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Mconnect\Giftcard\Helper\Data');
        
        $days_before_expiry = $helper->getDaysBeforeExpiry();
        
        $collection = $this->_giftcodeFactory->create();
        foreach ($collection as $giftcode) {
            $expiry_date        = $giftcode->getExpiryDate();
            $status                 = $giftcode->getStatus();
            $initial_value      = $giftcode->getInitialValue();
            $current_balance    = $giftcode->getCurrentBalance();
            $product_id         = $giftcode->getProductId();
        
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($product_id);
            $card_type = $product->getGcCardType();
        
            $today          = date("Y-m-d H:i:s");
            $expiry_date    = date("Y-m-d H:i:s", strtotime($expiry_date));
            
            if ($expiry_date < $today) {
                try {
                    if ($days_before_expiry == 0) {
                        /* Receiver Detail  */
                        $receiverInfo = [
                            'name' => $giftcode->getRecipientName(),
                            'email' => $giftcode->getRecipientEmail()
                        ];
                    
                        /* Sender Detail  */
                        $senderInfo = $helper->getEmailSender();
                        
                        $pricingHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
                        
                        /* Assign values for your template variables  */
                        $vars = [];
                        $vars['recipient_name'] = $giftcode->getRecipientName();
                        $vars['giftcode']       = $giftcode->getGiftcode();
                        $vars['balance']        = $pricingHelper->currency($giftcode->getCurrentBalance(), true, false);
                        $vars['expiry_date']    = $giftcode->getExpiryDate();
                        
                        $templateId = $helper->getExpiryTemplate();
                        if ($card_type != \Mconnect\Giftcard\Model\Product\Attribute\Source\CardType::PHYSICAL) {
                            try {
                                $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                                    $vars,
                                    $senderInfo,
                                    $receiverInfo,
                                    $templateId
                                );
                            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                                $this->_logger->info($e->getMessage());
                            } catch (\RuntimeException $e) {
                                $this->_logger->info($e->getMessage());
                            } catch (\Exception $e) {
                                // @codingStandardsIgnoreLine
                                $this->_logger->info(
                                    $e->getMessage().'Something went wrong while sending giftcard expiry email.'
                                );
                            }
                        }
                    }
                    
                    $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
                    $coupon->load($giftcode->getGiftcode(), 'giftcode');
                    
                    $coupon->setStatus(\Mconnect\Giftcard\Model\Giftcode::STATUS_EXPIRED);
                    $coupon->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->info($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->_logger->info($e->getMessage());
                } catch (\Exception $e) {
                    $this->_logger->info($e->getMessage().'Something went wrong while giftcard status update.');
                }
            } elseif ($current_balance <= 0) {
                try {
                    $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
                    $coupon->load($giftcode->getGiftcode(), 'giftcode');
                    $coupon->setStatus(\Mconnect\Giftcard\Model\Giftcode::STATUS_USED);
                    $coupon->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->info($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->_logger->info($e->getMessage());
                } catch (\Exception $e) {
                    $this->_logger->info($e->getMessage().'Something went wrong while giftcard status update.');
                }
            } elseif ($current_balance > 0 && $initial_value != $current_balance) {
                try {
                    $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
                    $coupon->load($giftcode->getGiftcode(), 'giftcode');
                    $coupon->setStatus(\Mconnect\Giftcard\Model\Giftcode::STATUS_PARTIALUSED);
                    $coupon->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->info($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->_logger->info($e->getMessage());
                } catch (\Exception $e) {
                    $this->_logger->info($e->getMessage().'Something went wrong while giftcard status update.');
                }
            }
        }
        
        return $this;
    }
    
    public function sendGiftcard()
    {
        $collection = $this->_giftcodeFactory->create();
        
        foreach ($collection as $giftcode) {
            $product_id = $giftcode->getProductId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
            
            $card_type = $product->getGcCardType();
            $is_physical = ($card_type == \Mconnect\Giftcard\Model\Product\Attribute\Source\CardType::PHYSICAL);
            
            if ($giftcode->getOrderId() && $giftcode->getQuoteItemId() && !$is_physical) {
                $helper = $objectManager->get('Mconnect\Giftcard\Helper\Data');
                
                $order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($giftcode->getOrderId());
                $is_mail_sent = $giftcode->getIsMailSent();
                    
                if (!$is_mail_sent) {
                    foreach ($order->getAllItems() as $orderItem) {
                        if ($orderItem->getQuoteItemId() == $giftcode->getQuoteItemId()) {
                            $product_options    = $orderItem->getProductOptions();
                            $options    = $product_options['info_buyRequest'];
                            
                            $time_zone  = $options['time_zone'];
                            $tz_date    = new \DateTime();
                            $tz_date->setTimezone(new \DateTimeZone($time_zone));
                            $today      = new \DateTime($tz_date->format('Y-m-d'));
                            $send_day   = new \DateTime(date("Y-m-d", strtotime($options['day_to_send'])));
                            
                            if ($today >= $send_day || (!isset($options['day_to_send']) || $options['day_to_send'] == '')) {
                                /* Receiver Detail  */
                                $receiverInfo = [
                                    'name' => $giftcode->getRecipientName(),
                                    'email' => $giftcode->getRecipientEmail()
                                ];
                                 
                                /* Sender Detail  */
                                
                                $senderInfo = $helper->getEmailSender();
                                
                                $_gc_email = $objectManager->create('Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email');
                            
                                $email_template     = $_gc_email->loadEmailDataById($giftcode->getEmailTemplate());
                
                                $templateId     = $email_template[0]['email_template'];
                                $template_image     = $giftcode->getImagePath();
                                $custom_image_path = $giftcode->getCustomImagePath();
                                
                                $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
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
                                $vars['sender_name']    = $giftcode->getSenderName();
                                $vars['recipient_name'] = $giftcode->getRecipientName();
                                $vars['balance']        = $pricingHelper->currency($giftcode->getCurrentBalance(), true, false);
                                $vars['card_image_base_url'] = $template_image;
                                $vars['headline']       = $giftcode->getHeadline();
                                $vars['message']        = $giftcode->getComment();
                                $vars['giftcode']       = $giftcode->getGiftcode();
                                
                                
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
                                            'name' => $giftcode->getSenderName(),
                                            'email' => $giftcode->getSenderEmail(),
                                        ];
                                        
                                        $vars = [];
                                        $vars['sender_name']    = $giftcode->getSenderName();
                                        $vars['recipient_name'] = $giftcode->getRecipientName();
                                        
                                        $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                                            $vars,
                                            $senderInfo,
                                            $receiverInfo,
                                            $templateId
                                        );
                                    }
                                    
                                    $coupon = $objectManager->create('\Mconnect\Giftcard\Model\Giftcode');
                                    $coupon->load($giftcode->getGiftcode(), 'giftcode');
                                    $coupon->setIsMailSent('1');
                                    $coupon->save();
                                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                                    $this->_logger->info($e->getMessage());
                                } catch (\RuntimeException $e) {
                                    $this->_logger->info($e->getMessage());
                                } catch (\Exception $e) {
                                    $this->_logger->info(
                                        $e->getMessage().'Something went wrong while sending giftcard email.'
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }
    
    public function sendExpiryNotification()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Mconnect\Giftcard\Helper\Data');
        
        $days_before_expiry = $helper->getDaysBeforeExpiry();
        
        $collection = $this->_giftcodeFactory->create();
        foreach ($collection as $giftcode) {
            $expiry_date        = $giftcode->getExpiryDate();
            $status                 = $giftcode->getStatus();
            $initial_value      = $giftcode->getInitialValue();
            $current_balance    = $giftcode->getCurrentBalance();
            $product_id         = $giftcode->getProductId();
        
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($product_id);
            $card_type = $product->getGcCardType();
            
            $notification_date  = date("Y-m-d H:i:s", strtotime("+$days_before_expiry day"));
            $expiry_date        = date("Y-m-d H:i:s", strtotime($expiry_date));
            
            if ($expiry_date < $notification_date) {
                /* Receiver Detail  */
                $receiverInfo = [
                    'name' => $giftcode->getRecipientName(),
                    'email' => $giftcode->getRecipientEmail()
                ];
                 
                 
                /* Sender Detail  */
                $senderInfo = $helper->getEmailSender();
                
                $pricingHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
                
                /* Assign values for your template variables  */
                $vars = [];
                $vars['recipient_name'] = $giftcode->getRecipientName();
                $vars['giftcode']       = $giftcode->getGiftcode();
                $vars['balance']        = $pricingHelper->currency($giftcode->getCurrentBalance(), true, false);
                $vars['expiry_date']    = $giftcode->getExpiryDate();
                
                $templateId = $helper->getExpiryTemplate();
                
                if ($card_type != \Mconnect\Giftcard\Model\Product\Attribute\Source\CardType::PHYSICAL) {
                    try {
                        $objectManager->get('Mconnect\Giftcard\Helper\Email')->sendGiftCardEmail(
                            $vars,
                            $senderInfo,
                            $receiverInfo,
                            $templateId
                        );
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->_logger->info($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->_logger->info($e->getMessage());
                    } catch (\Exception $e) {
                        $this->_logger->info(
                            $e->getMessage().'Something went wrong while sending giftcard expiry email.'
                        );
                    }
                }
            }
        }
        
        return $this;
    }
}
