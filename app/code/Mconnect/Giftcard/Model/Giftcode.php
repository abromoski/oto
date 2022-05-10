<?php
namespace Mconnect\Giftcard\Model;
 
class Giftcode extends \Magento\Framework\Model\AbstractModel
{
    
    const STATUS_INACTIVE   = 0;
    const STATUS_ACTIVE     = 1;
    const STATUS_USED       = 2;
    const STATUS_PARTIALUSED = 3;
    const STATUS_EXPIRED    = 4;
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    
    protected function _construct()
    {
        $this->_init('Mconnect\Giftcard\Model\ResourceModel\Giftcode');
    }
    
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive'),
            self::STATUS_USED => __('Used'),
            self::STATUS_PARTIALUSED => __('Partially Used'),
            self::STATUS_EXPIRED => __('Expired')
        ];
    }
}
