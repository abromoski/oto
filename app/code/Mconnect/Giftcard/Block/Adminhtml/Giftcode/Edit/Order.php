<?php
namespace Mconnect\Giftcard\Block\Adminhtml\Giftcode\Edit;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Mconnect\Giftcard\Model\GiftcodeFactory;

class Order extends Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $giftcodeFactory;
    protected $orderFactory;
        
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        GiftcodeFactory $giftcode,
        \Mconnect\Giftcard\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->giftcodeFactory = $giftcode;
        $this->orderFactory = $orderFactory;
        
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftcode_orders_grid');
        $this->setDefaultSort('giftcode_id');
        $this->setUseAjax(true);
        
        if (!$this->_isAllowedAction('Mconnect_Giftcard::giftcode')) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * Add filter
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('giftcode_id');
        $model = $this->giftcodeFactory->create();
        if ($id) {
            $model->load($id);
        }
        $ordercollection = $this->orderFactory->create();
        $ordercollection->addFieldToFilter('giftcode_id', ['eq' => $model->getId()]);
        
        $this->setCollection($ordercollection);
        return parent::_prepareCollection();
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $id = $this->getRequest()->getParam('giftcode_id');
        $model = $this->giftcodeFactory->create();
        if ($id) {
            $model->load($id);
        }
        
        $collection = $this->orderFactory->create();
        $collection->addFieldToFilter('giftcode_id', ['eq' => $model->getId()]);
        
        $this->addColumn(
            'gfo_id',
            [
                'header' => __('ID'),
                'index' => 'gfo_id',
                'header_css_class' => 'col-gfo_id',
                'column_css_class' => 'col-gfo_id',
                'type' => 'number'
            ]
        );
        
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order'),
                'index' => 'increment_id',
                'header_css_class' => 'col-increment_id',
                'column_css_class' => 'col-increment_id'
            ]
        );
        
        $this->addColumn(
            'bill_name',
            [
                'header' => __('Bill-to Name'),
                'index' => 'bill_name',
                'header_css_class' => 'col-bill_name',
                'column_css_class' => 'col-bill_name'
            ]
        );
        
        $this->addColumn(
            'ship_name',
            [
                'header' => __('Ship-to Name'),
                'index' => 'ship_name',
                'header_css_class' => 'col-ship_name',
                'column_css_class' => 'col-ship_name'
            ]
        );
        
        $this->addColumn(
            'order_total',
            [
                'header' => __('Order Total'),
                'index' => 'order_total',
                'header_css_class' => 'col-order_total',
                'column_css_class' => 'col-order_total',
                'type'  => 'price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode()
            ]
        );
        
        $this->addColumn(
            'spent_amount',
            [
                'header' => __('Spent Amount'),
                'index' => 'spent_amount',
                'header_css_class' => 'col-spent_amount',
                'column_css_class' => 'col-spent_amount',
                'type'  => 'price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode()
            ]
        );
        
        $this->addColumn(
            'purchage_date',
            [
                'header' => __('Purchage Date'),
                'index' => 'purchage_date',
                'header_css_class' => 'col-purchage_date',
                'column_css_class' => 'col-purchage_date',
                'type' => 'datetime'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            'giftcard/giftcode/order',
            ['_current' => true]
        );
    }
    
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
