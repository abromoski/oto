<?php
namespace Mconnect\Giftcard\Model\Product\Type;

class Giftcard extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'giftcardproduct';
    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
