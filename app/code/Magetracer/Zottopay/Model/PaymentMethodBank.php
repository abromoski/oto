<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetracer\Zottopay\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;
use Magetracer\Zottopay\Helper\Data as HelperData;

class PaymentMethodBank extends AbstractMethod
{
    const CODE = 'magetracerzottopaybank';
    const POST = "[POST to ZOTTO]";
	
	/**
     * @var HelperData
     */
    protected $_helperData;
	
    protected $_code = self::CODE;
    
    protected $_isInitializeNeeded      = true;
    
    protected $_formBlockType = 'Magetracer\Zottopay\Block\Form';
    protected $_infoBlockType = 'Magetracer\Zottopay\Block\Info';
 
    protected $_isGateway                   = false;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_canSaveCc                   = false;
    
	protected $url;
    protected $urlBuilder;
    protected $_moduleList;
    protected $checkoutSession;
    protected $_orderFactory;
 
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
		HelperData $helperData, 
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []){
		$this->_helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        $this->_moduleList = $moduleList;
        $this->checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data);
    }
    
    /**
     *  Redirect URL
     *
     *  @return   string Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('magetracerzottopay/payment/redirectbank', ['_secure' => true]);
    }

    /**
     *  Gateway URL
     *
     *  @return   string Gateway URL
     */
    public function getGatewayUrl()
    {
		if($this->getConfigData('testmode') == 1)
		{
			$this->url = "http://ciboapp.me/apitest/api/v1/checkoutpay/payment";
			
			
		}else{
			$this->url = "https://payment.z-pay.co.uk/api/v1/checkoutpay/payment";
			
		}
        return $this->url;
    }


    public function canUseForCurrency($currencyCode)
    {
		if($currencyCode == "GBP")
		{
			return true;
		}
         return false;  
    }
    
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        //$order = $payment->getOrder();

        $state = $this->getConfigData('new_order_status');

        //$state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }
   
    public function getCheckoutParameter()
    {
        $orderIncrementId = $this->checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $productDetails = $this->getProductItems($order->getAllItems());


        $order_currency    = $order->getOrderCurrencyCode();
        $order_amount      = sprintf('%.2f', $order->getGrandTotal());


        $merchant_key           = $this->getConfigData('apikey');
		$secret_key           = $this->getConfigData('secretkey');
        $email     = $this->OceanHtmlSpecialChars($order->getCustomerEmail());
        $phone     = $billing->getTelephone();
        $country   = $billing->getCountryId();
        $callback_url = $this->urlBuilder->getUrl('magetracerzottopay/payment/ipn', ['_secure' => true]);
		$cancel_url = $this->urlBuilder->getUrl('magetracerzottopay/payment/cancel', ['_secure' => true]);
		$error_url = $this->urlBuilder->getUrl('magetracerzottopay/payment/failed', ['_secure' => true]);
		$failed_url = $this->urlBuilder->getUrl('magetracerzottopay/payment/failed', ['_secure' => true]);
		$success_url = $this->urlBuilder->getUrl('magetracerzottopay/payment/success', ['_secure' => true]);
		$user_id = "";
        $user_id = $order->getCustomerId();
		$redirect_type = 2;
		
		$macstring1 = $order_amount.$cancel_url.$callback_url.$order_currency.$error_url.$failed_url.$merchant_key.$orderIncrementId.$success_url.$user_id.$secret_key;
        $macstring = str_replace(" ","",$macstring1); 
        $machash=  hash('sha256', $macstring);
		
		$data = array(
            'merchant_key' => $merchant_key,
            'amount' => $order_amount,
            'email' => $email,
            'country' => $country,
            'user_id'=> $user_id,
            'currency' => $order_currency,
            'redirect_type' => $redirect_type,
            'phone' => $phone,
            'mac_string' => $machash,
            'success_url' => $success_url,
            'callback_url' => $callback_url,
            'error_url' => $error_url,
            'failed_url' => $failed_url,
            'back_url' => $cancel_url,
            'order_id' => $orderIncrementId,
        );
		
        

		//$this->_helperData->postLog(self::POST, $data);


        return $data;
    }
    
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        
        if (parent::isAvailable($quote) && $quote){
            return true;
        }
        return false;
    }
   
    
    
    function OceanHtmlSpecialChars($parameter){

        $parameter = trim($parameter);

        $parameter = str_replace(array("<",">","'","\""),array("&lt;","&gt;","&#039;","&quot;"),$parameter);
        
        return $parameter;

    }
	
	

}
