<?php

namespace Etatvasoft\Banner\Block\Adminhtml;

/**
 * Class Banner
 * @package Etatvasoft\Banner\Block\Adminhtml
 */
class Banner extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Etatvasoft_Banner';
        $this->_controller = 'adminhtml';
        $this->_headerText = __('Banner');
        $this->_addButtonLabel = __('Add New Banner');
        parent::_construct();
    }
}
