<?php
namespace Mconnect\Giftcard\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_GENERAL_ENABLE       = 'giftcard/general/enable';
    const XML_PATH_MSG_MAX_LENGTH       = 'giftcard/product/msg_max_length';
    const XML_PATH_PREVIEW_CARD         = 'giftcard/product/preview_card';
    const XML_PATH_ALLOW_MESSAGE        = 'giftcard/options/allow_message';
    const XML_PATH_ALLOW_DELIVERY_DATE  = 'giftcard/options/choose_delivery_date';
    const XML_PATH_CHANGE_IMAGE         = 'giftcard/product/change_image';
    const XML_PATH_MAX_IMG_SIZE         = 'giftcard/product/max_img_size';
    const XML_PATH_PRODUCT_TYPE         = 'giftcard/general/product_type';
    const XML_PATH_USE_FOR_SELF         = 'giftcard/options/use_for_self';
    const XML_PATH_WITH_COUPON_CODES    = 'giftcard/options/with_coupon_codes';
    const XML_PATH_DAYS_BEFORE_EXPIRY   = 'giftcard/options/days_before_expiry';
    
    const XML_PATH_DISPLAY_INFO         = 'giftcard/cart/display_info';
    const XML_PATH_CARD_AS_PIMAGE       = 'giftcard/cart/card_as_pimage';
    const XML_PATH_SHOW_CARDBOX         = 'giftcard/cart/show_cardbox';
    
    const XML_PATH_EMAIL_SENDER         = 'giftcard/email/email_sender';
    const XML_PATH_EXPIRY_TEMPLATE      = 'giftcard/email/expiry_template';
    const XML_PATH_CONFIRM_TO_SENDER    = 'giftcard/email/confirm_to_sender';
    const XML_PATH_SEND_COPY_TO         = 'giftcard/email/send_copy_to';
    const XML_PATH_SENDER_CONFIRMATION_TEMPLATE     = 'giftcard/email/sender_confirmation_template';
    const XML_PATH_NOTIFY_RECIPIENT_REFUNDS     = 'giftcard/email/notify_recipient_refunds';
    const XML_PATH_NOTIFY_RECIPIENT_REFUNDS_TEMPLATE    = 'giftcard/email/notify_recipient_refunds_template';
    
    const XML_PATH_GENERAL_LOCALE_TIMEZONE     = 'general/locale/timezone';
    
    protected $_timezone;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $httpFactory;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\Framework\Io\File
     */
    protected $_ioFile;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Config\Model\Config\Source\Locale\Timezone $timezone
    ) {
        $this->filesystem = $filesystem;
        $this->httpFactory = $httpFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_ioFile = $ioFile;
        $this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
        $this->_timezone = $timezone;
        parent::__construct($context);
    }
    
    public function getGiftcardEnabled()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_GENERAL_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     *
     * @param string $imageFile
     * @return bool
     */
    public function removeImage($imageFile)
    {
        $io = $this->_ioFile;
        $io->open(['path' => $this->getBaseDir()]);
        if ($io->fileExists($imageFile)) {
            return $io->rm($imageFile);
        }
        return false;
    }
    
    /**
     * Return the base media directory for images
     *
     * @return string
     */
    public function getBaseDir()
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(self::MEDIA_PATH);
        return $path;
    }
    
    /**
     * Return the Base URL for images
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . '/' . self::MEDIA_PATH;
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getMsgMaxLength()
    {
        $max_length = $this->scopeConfig
            ->getValue(self::XML_PATH_MSG_MAX_LENGTH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $max_length = abs((int)$max_length);
        
        if ($max_length > 250 || $max_length <= 0) {
            $max_length = 250;
        }
        return $max_length;
    }
    
    public function isPreviewCard()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_PREVIEW_CARD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getAllowMessage()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_ALLOW_MESSAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getAllowDeliveryDate()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_ALLOW_DELIVERY_DATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTimezones()
    {
        return $this->_timezone->toOptionArray();
    }
    
    public function getSiteTimezone()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_GENERAL_LOCALE_TIMEZONE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function canChangeImage()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_CHANGE_IMAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getMaxImageSize()
    {
        $size = $this->scopeConfig
            ->getValue(self::XML_PATH_MAX_IMG_SIZE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (intval($size) <= 0) {
            $size = 500;
        }
        return $size;
    }

    public function getCartDisplayInfo()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_DISPLAY_INFO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getCartCardAsPimage()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_CARD_AS_PIMAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getAllowedProductTypes()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_PRODUCT_TYPE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getUseForSelf()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_USE_FOR_SELF, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getUseWithCouponCodes()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_WITH_COUPON_CODES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function canShowCardbox()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SHOW_CARDBOX, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getDaysBeforeExpiry()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_DAYS_BEFORE_EXPIRY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getExpiryTemplate()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_EXPIRY_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getEmailSender()
    {
        $sender_contact = $this->scopeConfig
            ->getValue(self::XML_PATH_EMAIL_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $xml1 = 'trans_email/ident_'.$sender_contact.'/name';
        $sender_name = $this->scopeConfig->getValue($xml1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $xml2 = 'trans_email/ident_'.$sender_contact.'/email';
        $sender_email = $this->scopeConfig->getValue($xml2, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $senderInfo = [
                'name' => $sender_name,
                'email' => $sender_email,
            ];
        
        return $senderInfo;
    }
    
    public function getConfirmToSender()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_CONFIRM_TO_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSenderConfirmationTemplate()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SENDER_CONFIRMATION_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getNotifyRecipientRefunds()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_NOTIFY_RECIPIENT_REFUNDS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getNotifyRecipientRefundsTemplate()
    {
        return $this->scopeConfig
        ->getValue(self::XML_PATH_NOTIFY_RECIPIENT_REFUNDS_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSendCopyTo()
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SEND_COPY_TO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	public function getUnserializeData($data)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
		if($version >= '2.2.0'){
			$returnData = $objectManager->get('Magento\Framework\Serialize\SerializerInterface')->unserialize($data);
		}
		else{
			
			$returnData = (array) unserialize($data);
		}
		
		return $returnData;		
	}
	public function getSerializeData($data)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
		if($version >= '2.2.0'){
			$returnData = $objectManager->get('Magento\Framework\Serialize\SerializerInterface')->serialize($data);
		}
		else{
			
			$returnData = serialize($data);
		}
		return $returnData;		
	}
}
