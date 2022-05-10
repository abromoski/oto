<?php
namespace Mconnect\Giftcard\Model\ResourceModel\Order;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
 
    protected $_idFieldName = 'gfo_id';
   
    protected function _construct()
    {
        $this->_init('Mconnect\Giftcard\Model\Order', 'Mconnect\Giftcard\Model\ResourceModel\Order');
        $this->_map['fields']['gfo_id'] = 'main_table.gfo_id';
    }
}
