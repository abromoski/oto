<?php
namespace Mconnect\Giftcard\Block\Adminhtml;

class Giftcode extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Mconnect_Giftcard';
        $this->_headerText = __('Gift Code');

        parent::_construct();
        
        if ($this->_isAllowedAction('Mconnect_Giftcard::save')) {
            $this->buttonList->update('add', 'label', __('Create New Gift Code'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
