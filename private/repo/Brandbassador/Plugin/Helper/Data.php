<?php

namespace Brandbassador\Plugin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleList;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\HTTP\Client\Curl;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as SalesRules;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroups;
use Brandbassador\Plugin\Logger\Logger;

class Data extends AbstractHelper
{
    const XML_PATH_HELLOWORLD = 'brandbassador/';
    const PLUGIN_NAME = 'Brandbassador_Plugin';

    public function __construct(Context $context, ModuleList $moduleList, StoreManager $storeManager, SalesRules $salesRules, CustomerGroups $customerGroups, Curl $curl, Config $config, Logger $logger) 
    {
        parent::__construct($context);

        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->salesRules = $salesRules;
        $this->customerGroups = $customerGroups;
        $this->curl = $curl;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Retrieve specific config value
     */
    public function getConfigValue($field, $storeId = null)
    {
        $value = $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
        $this->logger->info('getConfigValue', [$field => $value]);
        return $value;
    }

    /**
     * Set specific config value
     */
    public function setConfigValue($field, $value)
    {
        $this->logger->info('setConfigValue', [$field => $value]);
        return $this->config->saveConfig($field, $value, 'default', 0);
    }

    /**
     * Retrieve general config value
     * specific to brandbassador plugin
     */
    public function getGeneralConfig($field, $storeId = null)
    {
        return $this->getConfigValue('brandkey');
    }

    /**
     * Set general config value
     * specific to brandbassador plugin
     */
    public function setGeneralConfig($field, $value)
    {
        return $this->setConfigValue(self::XML_PATH_HELLOWORLD . 'general/' . $field, $value);
    }

    /**
     * Retrieve environment configuration
     */
    public function getEnvironment()
    {
        $env = $this->getConfigValue('environment');

        if ($env == self::DEVELOPMENT || $env == self::STAGING) {
            return $env;
        }

        return self::PRODUCTION;
    }

    /**
     * Retrieve base API Url for Brandbassador Platform.
     */
    public function getBrandbassadorApiUrl() {
        $apiUrl = 'https://api.brandbassador.com';
        return $apiUrl;
    }


    /**
     * Get Package version from config file
     */
    public function getName()
    {   
        return self::PLUGIN_NAME;
    }

    /**
     * Get Package version from config file
     */
    public function getVersion()
    {   
        return $this->moduleList->getOne($this->getName())['setup_version'];
    }

}