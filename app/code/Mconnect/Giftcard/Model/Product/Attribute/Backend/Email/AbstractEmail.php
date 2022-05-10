<?php
// @codingStandardsIgnoreFile

namespace Mconnect\Giftcard\Model\Product\Attribute\Backend\Email;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
//use Magento\Customer\Api\GroupManagementInterface;
use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Catalog product abstract group price backend attribute model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractEmail extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;
    
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Error message when duplicates
     *
     * @abstract
     * @return string
     */
    abstract protected function _getDuplicateErrorMessage();

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Model\Product\Type $catalogProductType
    ) {
        $this->_catalogProductType = $catalogProductType;
        
        $this->_storeManager = $storeManager;
        $this->_helper = $catalogData;
        $this->_config = $config;
        $this->localeFormat = $localeFormat;
        //parent::__construct($currencyFactory, $storeManager, $catalogData, $config, $localeFormat);
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    abstract protected function _getResource();

    /**
     * Validate group price data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Phrase|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $emailRows = $object->getData($attribute->getName());
        $emailRows = array_filter((array)$emailRows);

        if (empty($emailRows)) {
            return true;
        }

        // validate per website
        $duplicates = [];
        foreach ($emailRows as $emailRow) {
            if (!empty($emailRow['delete'])) {
                continue;
            }
            $compare = implode(
                '-',
                [$emailRow['store_id'], $emailRow['email_template']]
            );
            if (isset($duplicates[$compare])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->_getDuplicateErrorMessage()));
            }

            $duplicates[$compare] = true;
        }
        
        return true;
    }

    /**
     * Assign group prices to product data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterLoad($object)
    {
        
        $storeId = $object->getStoreId();
        
        if ($storeId == '0') {
            $data = $this->_getResource()->loadEmailData(
                $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField())
            );
        } else {
            $data = $this->_getResource()->loadEmailData(
                $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()),
                $storeId
            );
        }
        
        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function afterSave($object)
    {
        $emailRows = $object->getData($this->getAttribute()->getName());
        if (null === $emailRows) {
            return $this;
        }

        $emailRows = array_filter((array)$emailRows);

        $old = [];
        $new = [];

        // prepare original data for compare
        $origPrices = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origPrices)) {
            $origPrices = [];
        }
        foreach ($origPrices as $data) {
            $old[$data['gemail_id']] = $data;
        }

        // prepare data for save
        $n=0;
        foreach ($emailRows as $data) {
            if (!isset($data['email_template']) || !empty($data['delete'])) {
                continue;
            }
            
            if (isset($data['gemail_id'])) {
                $new[$data['gemail_id']] = $data;
            } else {
                $new["new".$n++] = $data;
            }
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        $productId = $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField());

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_getResource()->deleteEmailData($productId, null, $data['gemail_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $email = new \Magento\Framework\DataObject($data);
                $email->setData('product_id', $productId);
                $this->_getResource()->saveEmailData($email);

                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {
                $email = new \Magento\Framework\DataObject([
                    'gemail_id' => $v['gemail_id'],
                    'email_template' => $v['email_template'],
                    'image_path' => $v['image_path'],
                    'store_id' => $v['store_id']
                ]);
                $this->_getResource()->saveEmailData($email);

                $isChanged = true;
            }
        }

        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }

    /**
     * Get resource model instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }
    
    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        return $this;
    }
}
