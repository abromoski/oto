<?php
namespace Mconnect\Giftcard\Controller\Adminhtml\Giftcode;

class Order extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mconnect_Giftcard::giftcode');
    }
    
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $layout = $this->_view->getLayout();
        $block = $layout->createBlock('Mconnect\Giftcard\Block\Adminhtml\Giftcode\Edit\Order');
        $this->getResponse()->appendBody($block->toHtml());
    }
}
