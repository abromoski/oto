<?php
namespace Mconnect\Giftcard\Block\Giftcode;

use \Magento\Framework\App\ObjectManager;
use \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory;

class ListGiftcode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'giftcode/list.phtml';

    /**
     * @var \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory
     */
    protected $_giftcodeCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /** @var \Mconnect\Giftcard\Model\ResourceModel\Giftcode\Collection */
    protected $giftcodes;

    /**
     * @var CollectionFactoryInterface
     */
    private $giftcodeCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory $giftcodeCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory $giftcodeCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_giftcodeCollectionFactory = $giftcodeCollectionFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Gift Codes'));
    }

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated
     */
    private function getGiftcodeCollectionFactory()
    {
        if ($this->giftcodeCollection === null) {
            $this->giftcodeCollection = $this->_giftcodeCollectionFactory->create();
        }
        return $this->giftcodeCollection;
    }

    /**
     * @return bool|\Mconnect\Giftcard\Model\ResourceModel\Giftcode\Collection
     */
    public function getGiftcodes()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
		if (!($customerEmail = $this->_customerSession->getCustomer()->getEmail())) {
			 return false;
		}
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
		$currentStoreId = $storeManager->getStore()->getStoreId();
				
        if (!$this->giftcodes) {
            $this->giftcodes = $this->getGiftcodeCollectionFactory()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'recipient_email',
                ['eq' => $customerEmail]
            )->addFieldToFilter(
			    'store_id',
				['eq' => $currentStoreId])
			->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->giftcodes;
    }
    
    public function formatCurrency($amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pricingHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        return $pricingHelper->currency($amount, true, false);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getGiftcodes()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'giftcard.giftcode.list.pager'
            )->setCollection(
                $this->getGiftcodes()
            );
            $this->setChild('pager', $pager);
            $this->getGiftcodes()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
