<?php

namespace Oto\HideDefaultStoreCode\Plugin\Model;

class HideDefaultStoreCode
{
    /**
     *
     * @var \Oto\HideDefaultStoreCode\Helper\Data 
     */
    protected $helper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * 
     * @param \Oto\HideDefaultStoreCode\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Oto\HideDefaultStoreCode\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * 
     * @param \Magento\Store\Model\Store $subject
     * @param string $url
     * @return string
     */
    public function afterGetBaseUrl(\Magento\Store\Model\Store $subject, $url)
    {
        if ($this->helper->isHideDefaultStoreCode()) {
            $url = str_replace('/'.$this->storeManager->getDefaultStoreView()->getCode().'/','/', $url);
        }
        return $url;
    }
}