<?php
namespace Mconnect\Giftcard\Controller\Catalog\Product;

use Magento\Framework\App\Filesystem\DirectoryList;

class Preview extends \Magento\Framework\App\Action\Action
{
    protected $_emailFactory;
    protected $_gc_email;
    
    protected $_storeManager;
    
    protected $_maliciousCode;
    protected $_pricingHelper;
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email $gc_email,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->_emailFactory = $emailFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->_storeManager = $storeManager;
        $this->_maliciousCode = $maliciousCode;
        $this->_pricingHelper = $pricingHelper;
        $this->_gc_email = $gc_email;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        
        $product        = $data['product'];
        $gc_email_id    = $data['gc_template'];
        $amount             = $data['gc_amount'];
        $custom_amount  = isset($data['gc_custom_amount']) ? $data['gc_custom_amount'] :'';
        $custom_image   = $data['gc_custom_image'];
        $recipient_name     = $data['gc_recipient_name'];
        $sender_name    = $data['gc_sender_name'];
        $headline       = $data['gc_headline'];
        $message        = $data['gc_message'];
        
        $email_template     = $this->_gc_email->loadEmailDataById($gc_email_id);
        
        $template_id    = $email_template[0]['email_template'];
        $template_image     = $email_template[0]['image_path'];
        
        $mediaurl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaurl = $mediaurl."catalog/product";
        
        if ($custom_image != "") {
            $template_image = $mediaurl.'/customgiftcard'.$custom_image;
        } else {
            $template_image = $mediaurl.$template_image;
        }
        
        if ($amount == "custom") {
            $amount = $custom_amount;
        }
        
        $vars = [];
        $vars['sender_name'] = $sender_name;
        $vars['recipient_name'] = $recipient_name;
        $vars['balance'] = $this->_pricingHelper->currency($amount, true, false);
        $vars['card_image_base_url'] = $template_image;
        $vars['headline'] = $headline;
        $vars['message'] = $message;
        $vars['giftcode'] = 'XXXXXXXXXX';

        $storeId = $this->_storeManager->getStore()->getStoreId();
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->_emailFactory->create();

        if ($id = (int)$template_id) {
            $template->load($id);
        } else {
            $template->loadDefault($template_id);
        }

        $template->setTemplateText($this->_maliciousCode->filter($template->getTemplateText()));

        $template->emulateDesign($storeId);
        $template->setVars($vars);
        $templateProcessed = $template->processTemplate();
        
        $this->getResponse()->setBody($templateProcessed);
    }
}
