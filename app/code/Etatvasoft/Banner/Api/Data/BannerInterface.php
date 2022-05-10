<?php
namespace Etatvasoft\Banner\Api\Data;

interface BannerInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BANNER_ID             = 'banner_id';
    const BANNER_TITLE          = 'banner_title';
    const BANNER_DESCRIPTION    = 'banner_description';
    const BANNER_IMAGE          = 'banner_image';
    const LABEL_BUTTON_TEXT     = 'label_button_text';
    const CALL_TO_ACTION        = 'call_to_action';
    const POSITION              = 'position';
    const IS_ACTIVE             = 'is_active';
    const CREATION_TIME         = 'creation_time';
    const UPDATE_TIME           = 'update_time';

    /**
     * Get banner ID
     *
     * @return int|null
     */
    public function getBannerId();

    /**
     * Get banner title
     *
     * @return string
     */
    public function getBannerTitle();

    /**
     * Get banner description
     *
     * @return string
     */
    public function getBannerDescription();

    /**
     * Get banner image
     *
     * @return string
     */

    public function getBannerImage();

    /**
     * Get banner button text
     *
     * @return string
     */
    public function getLabelButtonText();

    /**
     * Get banner action
     *
     * @return string
     */
    public function getCallToAction();

    /**
     * Get banner position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set banner id
     *
     * @param int $bannerId
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setBannerId($bannerId);

    /**
     * Set Banner title
     *
     * @param string $bannerTitle
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setBannerTitle($bannerTitle);

    /**
     * Set bannerdescription
     *
     * @param string $bannerDescription
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setBannerDescription($bannerDescription);

    /**
     * Set bannerimage
     *
     * @param string $bannerImage
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setBannerImage($bannerImage);

    /**
     * Set button text
     *
     * @param string $labelbuttonText
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setLabelButtonText($labelbuttonText);

    /**
     * Set action
     *
     * @param string $calltoAction
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setCallToAction($calltoAction);

    /**
     * @param int|null $position
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setPosition($position);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setIsActive($isActive);

    /**
     * Set creationTime
     *
     * @param string $creationTime
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set updateTime
     *
     * @param string $updateTime
     * @return Etatvasoft\Banner\Api\Data\BannerInterface
     */
    public function setUpdateTime($updateTime);
}
