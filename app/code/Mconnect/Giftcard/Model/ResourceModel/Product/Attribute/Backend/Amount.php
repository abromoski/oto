<?php
namespace Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend;

use Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Amount\AbstractAmount;

/**
 * Catalog product Giftcard email backend attribute model
 *
 */
class Amount extends AbstractAmount
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mconnect_giftcode_price', 'price_id');
    }
}
