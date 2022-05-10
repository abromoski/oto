<?php

namespace OrangeCompany\Learning\Plugin;

use Magento\Catalog\Helper\Product\View as ProductViewHelper;
use Magento\Framework\View\Result\Page;

/**
 * Class AddBodyClassToProductPagePlugin
 */
class AddBodyClassToProductPagePlugin
{
    /**
     * Adding a custom class to body
     *
     * @param ProductViewHelper $subject
     * @param Page $resultPage
     * @param $product
     * @param $params
     *
     * @return array
     */
    public function beforeInitProductLayout(
        ProductViewHelper $subject,
        Page $resultPage,
        $product,
        $params
    ): array {
        $pageConfig = $resultPage->getConfig();

      

            $product = $block->getProduct();
            $attribute_value = $_product->getAttributeText('moment_select'); 
            if (!empty($attribute_value)) {


            $pageConfig->addBodyClass('$attribute_value');
        }

        return [$resultPage, $product, $params];
    }
}