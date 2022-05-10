<?php
namespace Mconnect\Giftcard\Model\ResourceModel\Giftcode;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
 
    protected $_idFieldName = 'giftcode_id';
   
    protected function _construct()
    {
        $this->_init('Mconnect\Giftcard\Model\Giftcode', 'Mconnect\Giftcard\Model\ResourceModel\Giftcode');
        $this->_map['fields']['giftcode_id'] = 'main_table.giftcode_id';
    }
}
