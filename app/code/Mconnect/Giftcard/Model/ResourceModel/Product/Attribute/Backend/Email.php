<?php
namespace Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend;

use Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email\AbstractEmail;

/**
 * Catalog product Giftcard email backend attribute model
 *
 */
class Email extends AbstractEmail
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mconnect_giftcode_email_templates', 'gemail_id');
    }

    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadEmailDataColumns($columns)
    {
        $columns = parent::_loadEmailDataColumns($columns);
        return $columns;
    }

    /**
     * Order by qty
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _loadEmailDataSelect($select)
    {
        //$select->order('qty');
        return $select;
    }
}
