<?php
namespace Mconnect\Giftcard\Block\Product\View\Type;

class Giftcard extends \Magento\Catalog\Block\Product\View\AbstractView
{
    public function getAmounts()
    {
        $product = $this->getProduct();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $_gcAmounts = $objectManager->create('Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Amount');
        
        $storeId = $product->getStoreId();
        $websiteId = null;
        
        if ($storeId == '0') {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = $storeManager->getStore($storeId)->getWebsiteId();
        }
        
        $gc_amounts = $_gcAmounts->loadPriceData($product->getId(), $websiteId);
        
        $gc_allow_open_amount = $product->getData('gc_allow_open_amount');
        $gc_open_amount_min = $product->getData('gc_open_amount_min');
        $gc_open_amount_max = $product->getData('gc_open_amount_max');
        
        $amounts = [];
        
        foreach ($gc_amounts as $amount) {
            $amounts[$amount['amount']] = $amount['amount'];
        }
        
        if ($gc_allow_open_amount && $gc_open_amount_min > 0 && $gc_open_amount_max > 0) {
            $amounts['custom'] = ['min'=> $gc_open_amount_min, 'max' => $gc_open_amount_max];
        }
        
        return $amounts;
    }
    
    public function getDesigns()
    {
        $product = $this->getProduct();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $_gcEmails = $objectManager->create('Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email');
        
        $storeId = $product->getStoreId();
        
        $gc_emails = $_gcEmails->loadEmailData($product->getId(), $storeId);
        
        $emails = [];
        $mediaurl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaurl = $mediaurl."catalog/product";
        foreach ($gc_emails as $email) {
            if ($email['image_path'] != "") {
                $emails[] = [ 'gemail_id' => $email['gemail_id'], 'image_path' => $mediaurl.$email['image_path'] ];
            } else {
                $placeholder = $this->getViewFileUrl('Mconnect_Giftcard::images/design_placeholder.jpg');
                $emails[] = [ 'gemail_id' => $email['gemail_id'], 'image_path' => $placeholder ];
            }
        }
        
        return is_array($emails)? $emails: [];
    }
}
