<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;

class AccessToken implements MessageInterface
{
    /**
     * Unique system message identity.
     */
    public const MESSAGE_IDENTITY = 'apptrian_facebookpixel_system_notification_access_token';
    
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor.
     *
     * @param \Apptrian\FacebookPixel\Helper\Data $helper
     */
    public function __construct(
        \Apptrian\FacebookPixel\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        $config = '';
        $display = true;
       
        $stores = $this->helper->storeManager->getStores();
       
        foreach ($stores as $store) {
            $config = $this->helper->getConfig(
                'apptrian_facebookpixel/server/access_token',
                $store->getId()
            );
            
            if (!empty($config) && $config != 'xxx') {
                $display = false;
                break;
            }
        }
       
        return $display;
    }

    /**
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __(
            'Please provide your Access Token. 
            Stores > 
            Configuration > 
            Apptrian Extensions > 
            Facebook Pixel and Conversions API > 
            Facebook Conversions API (Server-Side Settings) > 
            Facebook Access Token'
        );
    }

    /**
     * Retrieve system message severity
     * Possible default system message types:
     * - MessageInterface::SEVERITY_CRITICAL
     * - MessageInterface::SEVERITY_MAJOR
     * - MessageInterface::SEVERITY_MINOR
     * - MessageInterface::SEVERITY_NOTICE
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_NOTICE;
    }
}
