<?php

namespace Mconnect\Giftcard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CheckoutCartProductAddAfterObserver implements ObserverInterface
{
    protected $giftData;
    protected $_gc_email;
    protected $_emailFactory;
    protected $_priceHelper;
	protected $_helper;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_request;
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Mconnect\Giftcard\Helper\Data $giftData,
        \Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email $gc_email,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
		\Mconnect\Giftcard\Helper\Data $helper
    ) {
    
        $this->giftData = $giftData;
        $this->_gc_email = $gc_email;
        $this->_priceHelper = $priceHelper;
        $this->_emailFactory = $emailFactory;
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
		$this->_helper = $helper;
    }
    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var \Magento\Quote\Model\Quote\Item $item */
        $disp_info = explode(",", $this->giftData->getCartDisplayInfo());
        
        $item       = $observer->getQuoteItem();
        
        if ($item->getProductType() != \Mconnect\Giftcard\Model\Product\Type\Giftcard::TYPE_CODE) {
            return;
        }
        
        $product    = $item->getProduct();
        $price_type     = $product->getGcPriceType();
        $percent    = $product->getGcPercentOfAmount();
        
        $additionalOptions = [];
        if ($additionalOption = $item->getOptionByCode('additional_options')) {
            //$additionalOptions = (array) unserialize($additionalOption->getValue());
			$additionalOptions = $this->_helper->getUnserializeData($additionalOption->getValue());
        }
        
        $data = $this->_request->getParams();
        
        $gc_amount          = $data['gc_amount'];
        $gc_custom_amount   = isset($data['gc_custom_amount'])? $data['gc_custom_amount'] :'';
        $gc_template        = isset($data['gc_template'])? $data['gc_template'] :'';
        $gc_custom_image    = isset($data['gc_custom_image']) ? $data['gc_custom_image'] : '';
        $gc_recipient_name  = isset($data['gc_recipient_name']) ? $data['gc_recipient_name'] : '';
        $gc_recipient_email = isset($data['gc_recipient_email']) ? $data['gc_recipient_email'] : '';
        $gc_sender_name     = isset($data['gc_sender_name']) ? $data['gc_sender_name'] : '';
        $gc_sender_email    = isset($data['gc_sender_email']) ? $data['gc_sender_email'] : '';
        $gc_headline        = isset($data['gc_headline']) ? $data['gc_headline'] : '';
        $gc_message         = isset($data['gc_message']) ? $data['gc_message'] : '';
        $day_to_send        = isset($data['day_to_send']) ? $data['day_to_send'] : '';
        $time_zone          = isset($data['time_zone']) ? $data['time_zone'] : '';
        
        if ($gc_amount == "custom") {
            $gc_amount = $gc_custom_amount;
        }
        
        if (intval($gc_amount)>0) {
            if ($price_type == 1 && $percent != '' && $percent > 0) {
                $card_cost = $gc_amount*$percent/100;
                $item->setOriginalCustomPrice($card_cost);
            } else {
                $item->setOriginalCustomPrice($gc_amount);
            }
        }
        
        if ($gc_custom_image != '') {
            $item->addOption([
                'code' => 'gc_custom_image',
                'value' => $gc_custom_image
            ]);
        }
        
        if (intval($gc_amount)>0) {
            $item->addOption([
                'code' => 'gc_amount',
                'value' => $gc_amount
            ]);
        }
        
        if (intval($gc_amount)>0 && in_array("amount", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('Card Value'),
                'value' => $this->_priceHelper->currency($gc_amount, true, false)
            ];
        }
        
        if ($gc_template != "") {
            $email_template     = $this->_gc_email->loadEmailDataById($gc_template);
            $image_path = $email_template[0]['image_path'];
            
            $item->addOption([
                'code' => 'gc_template',
                'value' => $gc_template
            ]);
            $item->addOption([
                'code' => 'gc_image_path',
                'value' => $image_path
            ]);
        }
        if ($gc_template != "" && in_array("giftcard_template_id", $disp_info)) {
            $tid = $email_template[0]['email_template'];
            
            $template = $this->_emailFactory->create();
            $template->load($tid);
            
            $additionalOptions[] = [
                'label' => __('Card Template'),
                'value' => $template->getTemplateCode()
            ];
        }
        
        if ($gc_sender_name != "" && in_array("customer_name", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('From'),
                'value' => $gc_sender_name
            ];
        }
        
        if ($gc_recipient_name != "" && in_array("recipient_name", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('To'),
                'value' => $gc_recipient_name
            ];
        }
        
        if ($gc_recipient_email != "" && in_array("recipient_email", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('To Email'),
                'value' => $gc_recipient_email
            ];
        }
        
        if ($day_to_send != "" && in_array("day_to_send", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('Day To Send'),
                'value' => $day_to_send
            ];
        }
        
        if ($time_zone != "" && in_array("timezone_to_send", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('Time Zone'),
                'value' => $time_zone
            ];
        }
        
        if ($gc_message != "" && in_array("message", $disp_info)) {
            $additionalOptions[] = [
                'label' => __('Message'),
                'value' => $gc_message
            ];
        }
        
        if ($gc_headline != "") {
            $item->addOption([
                'code' => 'headline',
                'value' => $gc_headline
            ]);
        }
        
        if ($gc_message != "") {
            $item->addOption([
                'code' => 'gc_message',
                'value' => $gc_message
            ]);
        }
        
        if ($day_to_send != "") {
            $item->addOption([
                'code' => 'day_to_send',
                'value' => $day_to_send
            ]);
        }
        
        if ($time_zone != "") {
            $item->addOption([
                'code' => 'time_zone',
                'value' => $time_zone
            ]);
        }
        
        if ($gc_recipient_email != "") {
            $item->addOption([
                'code' => 'gc_recipient_email',
                'value' => $gc_recipient_email
            ]);
        }
        
        if ($gc_sender_email != "") {
            $item->addOption([
                'code' => 'gc_sender_email',
                'value' => $gc_sender_email
            ]);
        }
        
        if (count($additionalOptions) > 0) {
            $item->addOption([
                'code' => 'additional_options',
                //'value' => serialize($additionalOptions)
				'value' => $this->_helper->getSerializeData($additionalOptions)
            ]);
        }
    }
}
