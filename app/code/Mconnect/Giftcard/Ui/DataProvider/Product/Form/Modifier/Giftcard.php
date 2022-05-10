<?php
namespace Mconnect\Giftcard\Ui\DataProvider\Product\Form\Modifier;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

use Mconnect\Giftcard\Model\Product\Type\Giftcard as GiftcardType;
use Mconnect\Giftcard\Model\Product\Attribute\Source\PriceType;

/**
 * Class GiftcardDataProvider
 */
class Giftcard extends AbstractModifier
{
    const FIELD_LIFETIME = 'gc_lifetime';
    const FIELD_EMAIL_TEMPLATES = 'gc_email_templates';
    const FIELD_ALLOW_MESSAGE = 'gc_allow_message';
    const FIELD_GIFTCARD_AMOUNT = 'gc_amounts';
    const FIELD_ALLOW_OPEN_AMOUNT = 'gc_allow_open_amount';
    const FIELD_OPEN_AMOUNT_MIN = 'gc_open_amount_min';
    const FIELD_OPEN_AMOUNT_MAX = 'gc_open_amount_max';
    const FIELD_PRICE_TYPE = 'gc_price_type';
    const FIELD_PERCENT_OF_AMOUNT = 'gc_percent_of_amount';
    
    const XPATH_CONFIG_GIFT_CARD_LIFE_TIME = 'giftcard/options/lifetime';
    const XPATH_CONFIG_GIFT_CARD_ALLOW_MESSAGE = 'giftcard/options/allow_message';
    
    protected $_templatesFactory;
    private $_emailConfig;
    protected $storeManager;
    protected $_priceType;
    protected $systemStore;
    protected $escaper;
    protected $urlBuilder;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Mconnect\Giftcard\Model\Product\Attribute\Source\PriceType $priceType,
        SystemStore $systemStore,
        Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        $this->_priceType = $priceType;
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        $value = '';
        $valueAllowMessage = '';

        if (isset($data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_LIFETIME])) {
            $value = $data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_LIFETIME];
        }

        if ('' === $value) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_LIFETIME] =
                $this->getValueFromConfig();
            $data[$modelId][static::DATA_SOURCE_DEFAULT]['use_config_' . static::FIELD_LIFETIME] = '1';
        }

        //for allow message config settings
        if (isset($data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_ALLOW_MESSAGE])) {
            $valueAllowMessage = $data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_ALLOW_MESSAGE];
        }

        if ('' === $valueAllowMessage) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_ALLOW_MESSAGE] =
                $this->getValueFromConfigAllowMessage();
            $data[$modelId][static::DATA_SOURCE_DEFAULT]['use_config_' . static::FIELD_ALLOW_MESSAGE] = '1';
        }
        if (2 == $valueAllowMessage) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT]['use_config_' . static::FIELD_ALLOW_MESSAGE] = '1';
        }
        
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === GiftcardType::TYPE_CODE) {
            $meta = $this->customizeLifeTimeField($meta);
            $meta = $this->customizeAllowMessageField($meta);
            $meta = $this->customizeEmailTemplates($meta);
            $meta = $this->customizeGiftcardAmounts($meta);
            $meta = $this->customizeAllowOpenAmounts($meta);
            $meta = $this->customizePriceType($meta);
        }
        return $meta;
    }

    /**
     * Customization of allow gift message field
     *
     * @param array $meta
     * @return array
     */
    protected function customizeLifeTimeField(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_LIFETIME);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_LIFETIME,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_LIFETIME, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_LIFETIME => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_LIFETIME,
                                    'imports' => [
                                        'disabled' =>
                                            '${$.parentName}.use_config_'
                                            . static::FIELD_LIFETIME
                                            . ':checked',
                                    ],
                                    'additionalClasses' => 'admin__field-x-small',
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                ],
                            ],
                        ],
                    ],
                    'use_config_' . static::FIELD_LIFETIME => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'number',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'description' => __('Use Config Settings'),
                                    'dataScope' => 'use_config_' . static::FIELD_LIFETIME,
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Get config value data
     *
     * @return string|null
     */
    protected function getValueFromConfig()
    {
        return $this->scopeConfig->getValue(
            static::XPATH_CONFIG_GIFT_CARD_LIFE_TIME,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    protected function customizeAllowMessageField(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_ALLOW_MESSAGE);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_ALLOW_MESSAGE,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_ALLOW_MESSAGE, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_ALLOW_MESSAGE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_ALLOW_MESSAGE,
                                    'imports' => [
                                        'disabled' =>
                                            '${$.parentName}.use_config_'
                                            . static::FIELD_ALLOW_MESSAGE
                                            . ':checked',
                                    ],
                                    'additionalClasses' => 'admin__field-x-small',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'prefer' => 'toggle',
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'use_config_' . static::FIELD_ALLOW_MESSAGE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'number',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'description' => __('Use Config Settings'),
                                    'dataScope' => 'use_config_' . static::FIELD_ALLOW_MESSAGE,
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }
    
    protected function getValueFromConfigAllowMessage()
    {
        return $this->scopeConfig->getValue(
            static::XPATH_CONFIG_GIFT_CARD_ALLOW_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    protected function customizeEmailTemplates($meta)
    {
        $emailTemplatesPath = $this->arrayManager->findPath(
            static::FIELD_EMAIL_TEMPLATES,
            $meta,
            null,
            'children'
        );

        if ($emailTemplatesPath) {
            $meta = $this->arrayManager->merge(
                $emailTemplatesPath,
                $meta,
                $this->getEmailTemplatesStructure($emailTemplatesPath, $meta)
            );
            $meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($emailTemplatesPath, 0, -3)
                . '/' . static::FIELD_EMAIL_TEMPLATES,
                $meta,
                $this->arrayManager->get($emailTemplatesPath, $meta)
            );
            $meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($emailTemplatesPath, 0, -2),
                $meta
            );
        }

        return $meta;
    }
    
    protected function getEmailTemplatesStructure($emailTemplatesPath, $meta)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'component' => 'Mconnect_Giftcard/js/dynamic-rows',
                        'label' => __('Email Templates'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($emailTemplatesPath . '/arguments/data/config/sortOrder', $meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'store_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'store_id',
                                        'label' => __('Store'),
                                        'options' => $this->getStores(),
                                        'value' => $this->getDefaultWebsite(),
                                        'visible' => $this->isMultiWebsites(),
                                        'disabled' => ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()),
                                    ],
                                ],
                            ],
                        ],
                        'email_template' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'email_template',
                                        'label' => __('Email Template'),
                                        'options' => $this->getEmailTemplates(),
                                    ],
                                ],
                            ],
                        ],
                        'image_path' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('Image Upload'),
                                        'componentType' =>  'image',
                                        'formElement' => 'fileUploader',
                                        'dataScope' => 'image_path',
                                        'dataType' => Media::NAME,
                                        'component' => 'Mconnect_Giftcard/js/giftcard-upload',
                                        'elementTmpl' => 'Mconnect_Giftcard/uploader',
                                        'mediaurl' => $this->storeManager->getStore()
                                            ->getBaseUrl(
                                                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                                            ),
                                        'uploadurl' => $this->urlBuilder
                                            ->getUrl(
                                                'giftcard/product/upload',
                                                ['isAjax' =>true]
                                            ),
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    protected function getWebsites()
    {
        
        $website = $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsite();
        $websites = [
            [
                'label' => __('All Websites'). ' [' . $website->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $product = $this->locator->getProduct();
        
        $websitesList = $this->storeManager->getWebsites();
        $productWebsiteIds = $product->getWebsiteIds();
        foreach ($websitesList as $website) {
            $websites[] = [
                'label' => $website->getName() . ' [' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        }
        
        return $websites;
    }
    
    protected function getStores()
    {
        $storeOptions = [
            [
                'label' => __('All Store Views'),
                'value' => 0,
            ]
        ];
        $product = $this->locator->getProduct();
            
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        $groupCollection = $this->systemStore->getGroupCollection();
        $storeCollection = $this->systemStore->getStoreCollection();
        $productWebsiteIds = $product->getWebsiteIds();
        foreach ($websiteCollection as $website) {
            $groups = [];
            /** @var \Magento\Store\Model\Group $group */
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $stores = [];
                    /** @var  \Magento\Store\Model\Store $store */
                    foreach ($storeCollection as $store) {
                        if ($store->getGroupId() == $group->getId()) {
                            $name = $this->escaper->escapeHtml($store->getName());
                            $stores[$name]['label'] = str_repeat(' ', 8) . $name;
                            $stores[$name]['value'] = $store->getId();
                        }
                    }
                    if (!empty($stores)) {
                        $name = $this->escaper->escapeHtml($group->getName());
                        $groups[$name]['label'] = str_repeat(' ', 4) . $name;
                        $groups[$name]['value'] = array_values($stores);
                    }
                }
            }
            if (!empty($groups)) {
                $name = $this->escaper->escapeHtml($website->getName());
                $storeOptions[$name]['label'] = $name;
                $storeOptions[$name]['value'] = array_values($groups);
            }
        }
            
        return $storeOptions;
    }
    
    protected function isScopeGlobal()
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(static::FIELD_EMAIL_TEMPLATES)
            ->isScopeGlobal();
    }
    
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }
    
    protected function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }
    
    protected function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }
    protected function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }
    
    protected function getEmailTemplates()
    {
        $collection = $this->_templatesFactory->create();
        $collection->load();
        
        $options = $collection->toOptionArray();
        $templateId = 'giftcard_email_giftcard_template';
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        return $options;
    }
    
    protected function customizeGiftcardAmounts($meta)
    {
        $emailTemplatesPath = $this->arrayManager->findPath(
            static::FIELD_GIFTCARD_AMOUNT,
            $meta,
            null,
            'children'
        );

        if ($emailTemplatesPath) {
            $meta = $this->arrayManager->merge(
                $emailTemplatesPath,
                $meta,
                $this->getGiftcardAmountsStructure($emailTemplatesPath, $meta)
            );
            $meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($emailTemplatesPath, 0, -3)
                . '/' . static::FIELD_GIFTCARD_AMOUNT,
                $meta,
                $this->arrayManager->get($emailTemplatesPath, $meta)
            );
            $meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($emailTemplatesPath, 0, -2),
                $meta
            );
        }

        return $meta;
    }
    
    protected function getGiftcardAmountsStructure($emailTemplatesPath, $meta)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Amounts'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($emailTemplatesPath . '/arguments/data/config/sortOrder', $meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'website_id',
                                        'label' => __('Website'),
                                        'options' => $this->getWebsites(),
                                        'value' => $this->getDefaultWebsite(),
                                        'visible' => $this->isMultiWebsites(),
                                        'disabled' => ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()),
                                    ],
                                ],
                            ],
                        ],
                        'amount' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Amount'),
                                        'enableLabel' => true,
                                        'dataScope' => 'amount',
                                        'addbefore' => $this->locator->getStore()
                                                                     ->getBaseCurrency()
                                                                     ->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    protected function customizeAllowOpenAmounts(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_ALLOW_OPEN_AMOUNT);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_ALLOW_OPEN_AMOUNT,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_ALLOW_OPEN_AMOUNT, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_ALLOW_OPEN_AMOUNT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_ALLOW_OPEN_AMOUNT,
                                    'additionalClasses' => 'admin__field-x-small',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'prefer' => 'toggle',
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    
                ],
            ]
        );

        //-----------------------------------------------------------
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_OPEN_AMOUNT_MIN);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_OPEN_AMOUNT_MIN,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_OPEN_AMOUNT_MIN, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                        'imports' => [
                            'visible' => 'product_form.product_form.gift-card-options.container_' . static::FIELD_ALLOW_OPEN_AMOUNT . '.'.static::FIELD_ALLOW_OPEN_AMOUNT.':checked'
                        ],
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_OPEN_AMOUNT_MIN => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_OPEN_AMOUNT_MIN,
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'addbefore' => $this->locator->getStore()
                                                                     ->getBaseCurrency()
                                                                     ->getCurrencySymbol(),
                                ],
                            ],
                        ],
                    ],
                    
                ],
            ]
        );

        //-----------------------------------------------------------
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_OPEN_AMOUNT_MAX);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_OPEN_AMOUNT_MAX,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_OPEN_AMOUNT_MAX, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                        'imports' => [
                            'visible' => 'product_form.product_form.gift-card-options.container_' . static::FIELD_ALLOW_OPEN_AMOUNT . '.'.static::FIELD_ALLOW_OPEN_AMOUNT.':checked'
                        ],
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_OPEN_AMOUNT_MAX => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_OPEN_AMOUNT_MAX,
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'addbefore' => $this->locator->getStore()
                                                                     ->getBaseCurrency()
                                                                     ->getCurrencySymbol(),
                                ],
                            ],
                        ],
                    ],
                    
                ],
            ]
        );
        return $meta;
    }
    
    protected function customizePriceType(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_PRICE_TYPE);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_PRICE_TYPE,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_PRICE_TYPE, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_PRICE_TYPE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_PRICE_TYPE,
                                    'formElement' => Select::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'options' => $this->_priceType->getAllOptions(),
                                ],
                            ],
                        ],
                    ],
                    
                ],
            ]
        );

        //-----------------------------------------------------------
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_PERCENT_OF_AMOUNT);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_PERCENT_OF_AMOUNT,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_PERCENT_OF_AMOUNT, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                        'imports' => [
                            'visible' => 'product_form.product_form.gift-card-options.container_'.static::FIELD_PRICE_TYPE.'.'.static::FIELD_PRICE_TYPE.':value'
                        ],
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::FIELD_PERCENT_OF_AMOUNT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_PERCENT_OF_AMOUNT,
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'addbefore' => '%',
                                ],
                            ],
                        ],
                    ],
                    
                ],
            ]
        );
        
        return $meta;
    }
    
    protected function addJsFileUpload()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Mconnect_Giftcard/js/test',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'template' => 'ui/form/components/complex',
                       
                    ],
                ],
            ],
            'children' => [ ],
        ];
    }
}
