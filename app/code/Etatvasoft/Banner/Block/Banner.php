<?php
namespace Etatvasoft\Banner\Block;

use Etatvasoft\Banner\Api\Data\BannerInterface;
use Etatvasoft\Banner\Model\ResourceModel\Banner\Collection as BannerCollection;

/**
 * Class Banner
 * @package Etatvasoft\Banner\Block
 */
class Banner extends \Magento\Framework\View\Element\Template implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Etatvasoft\Banner\Model\ResourceModel\Banner\CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Etatvasoft\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory ,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Etatvasoft\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->bannerCollectionFactory = $bannerCollectionFactory;
    }

    /**
     * @return \Etatvasoft\Banner\Model\ResourceModel\Banner\Collection
     */
    public function getBanner()
    {
        if (!$this->hasData('banner')) {
            $banner = $this->bannerCollectionFactory
                ->create()
                ->addFilter('is_active', 1)
                ->addOrder(
                    BannerInterface::POSITION,
                    BannerCollection::SORT_ORDER_ASC
                );
            $this->setData('banner', $banner);
        }
        return $this->getData('banner');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Etatvasoft\Banner\Model\Banner::CACHE_TAG . '_' . 'list'];
    }

    /**
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
