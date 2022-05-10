<?php
namespace Mconnect\Giftcard\Model\Product\Type\Giftcard;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{  
    protected $_storeManager;
    protected $_gcAmounts;
    
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Amount $amount
    ) {
        $this->_storeManager = $storeManager;
        $this->_gcAmounts = $amount;
        
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );
    }
    
    /**
     * Default action to get price of product
     *
     * @param Product $product
     * @return float
     */
    public function getPrice($product)
    {
        $storeId = $product->getStoreId();
        $websiteId = null;
        
        if ($storeId == '0') {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }
        
        $gc_amounts = $this->_gcAmounts->loadPriceData($product->getId(), $websiteId);
        
        $gc_allow_open_amount = $product->getData('gc_allow_open_amount');
        $gc_open_amount_min = $product->getData('gc_open_amount_min');
        $gc_open_amount_max = $product->getData('gc_open_amount_max');
        
        if (count($gc_amounts) > 0 && (!$gc_allow_open_amount || $gc_open_amount_min <= 0 )) {
            $amounts = [];
            $gc_amounts[0];
            
            foreach ($gc_amounts as $gc_amount) {
                $amounts[] = $gc_amount['amount'];
            }
            
            if (count($amounts) > 0) {
                return min($amounts);
            } else {
                return 0;
            }
        } elseif ($gc_allow_open_amount) {
            return $gc_open_amount_min;
        } else {
            return 0;
        }
    }
    
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = $this->getBasePrice($product, $qty);
        $product->setFinalPrice($finalPrice);
        $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);
        $finalPrice = $product->getData('final_price');

        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);
        return $finalPrice;
    }
}
