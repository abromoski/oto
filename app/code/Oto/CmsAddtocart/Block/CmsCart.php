<?php
namespace Oto\CmsAddtocart\Block;

class CmsCart extends \Magento\Framework\View\Element\Template
{
    protected $productRepository;

    protected $listProductBlock;

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->productRepository = $productRepository;
        $this->listProductBlock = $listProductBlock;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getAddToCartUrl()
    {
        if ($_product = $this->getProduct()) {
            return $this->listProductBlock->getAddToCartPostParams($_product);
        }

        return '#';
    }

    public function getProduct()
    {
        if ($product_id = $this->getProductId()) {
            $_product = $this->productRepository->getById($product_id);
            if ($_product) {
                return $_product;
            }
        }

        return false;
    }
}