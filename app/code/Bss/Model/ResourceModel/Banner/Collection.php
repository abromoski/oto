<?php
namespace Etatvasoft\Banner\Model\ResourceModel\Banner;

/**
 * Class Collection
 * @package Etatvasoft\Banner\Model\ResourceModel\Banner
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $idFieldName = 'banner_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Etatvasoft\Banner\Model\Banner', 'Etatvasoft\Banner\Model\ResourceModel\Banner');
    }
}
