<?php

namespace Etatvasoft\Banner\Model;

use Etatvasoft\Banner\Api\Data\BannerInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Banner
 * @package Etatvasoft\Banner\Model
 */
class Banner extends \Magento\Framework\Model\AbstractModel implements BannerInterface, IdentityInterface
{

    /**
     * base media path for banner's image
     */
    const BASE_MEDIA_PATH = 'etatvasoft_banner';

    /**
     * Banner's Status enabled
     */
    const STATUS_ENABLED = 1;
    /**
     *Banner's Status disabled
     */
    const STATUS_DISABLED = 0;

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'etatvasoft_banner';

    /**
     * @var string
     */
    protected $cacheTag = 'etatvasoft_banner';

    /**
     * Prefix of model banner names
     *
     * @var string
     */
    protected $eventPrefix = 'etatvasoft_banner';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */

    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */

    protected function _construct()
    {
        $this->_init('Etatvasoft\Banner\Model\ResourceModel\Banner');
    }

    /**
     * Retrive Model Title
     * @param  boolean $plural
     * @return string
     */

    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Banners' : 'Banner';
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get banner ID
     *
     * @return int|null
     */
    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }

    /**
     * Get banner title
     *
     * @return string|null
     */

    public function getBannerTitle()
    {
        return $this->getData(self::BANNER_TITLE);
    }

    /**
     * Get banner description
     * @return string|null
     */
    public function getBannerDescription()
    {
        return $this->getData(self::BANNER_DESCRIPTION);
    }

    /**
     * Get banner image
     * @return string|null
     */
    public function getBannerImage()
    {

        return $this->getData(self::BANNER_IMAGE);
    }

    /**
     * Retrieve true if banner is active
     * @return boolean [description]
     */

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get button text for action of banner
     *
     * @return string|null
     */

    public function getLabelButtonText()
    {
        return $this->getData(self::LABEL_BUTTON_TEXT);
    }

    /**
     * Get action of banner button
     *
     * @return string|null
     */

    public function getCallToAction()
    {
        return $this->getData(self::CALL_TO_ACTION);
    }

    /**
     * Get banner position
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */

    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */

    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Is active
     *
     * @return bool|null
     */

    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set banner id
     *
     * @param int $bannerId
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }

    /**
     * Set banner title
     *
     * @param string $bannertitle
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setBannerTitle($bannerTitle)
    {
        return $this->setData(self::BANNER_TITLE, $bannerTitle);
    }

    /**
     * Set banner description
     *
     * @param string $bannerDescription
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setBannerDescription($bannerDescription)
    {
        return $this->setData(self::BANNER_DESCRIPTION, $bannerDescription);
    }

    /**
     * Set banner image
     *
     * @param string $bannerImage
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setBannerImage($bannerImage)
    {
        return $this->setData(self::BANNER_IMAGE, $bannerImage);
    }

    /**
     * Set button text
     *
     * @param string $labelbuttonText
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setLabelButtonText($labelbuttonText)
    {
        return $this->setData(self::LABEL_BUTTON_TEXT, $labelbuttonText);
    }

    /**
     * Set  calltoaction
     *
     * @param string $calltoAction
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setCallToAction($calltoAction)
    {
        return $this->setData(self::CALL_TO_ACTION, $calltoAction);
    }

    /**
     * @param int|null $position
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Set event creation time
     *
     * @param string $creation_time
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setCreationTime($creation_time)
    {
        return $this->setData(self::CREATION_TIME, $creation_time);
    }

    /**
     * Set update time
     *
     * @param string $update_time
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setUpdateTime($update_time)
    {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }

    /**
     * Set is active
     *
     * @param int|bool $is_active
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */

    public function setIsActive($is_active)
    {
        return $this->setData(self::IS_ACTIVE, $is_active);
    }
}
