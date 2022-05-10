<?php 

namespace Magetracer\Zottopay\Controller\Payment; 


use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magetracer\Zottopay\Helper\Data as HelperData;

class Cancel extends \Magento\Framework\App\Action\Action
{

    const BrowserReturn = "[Browser Return]";
	
	/**
     * @var HelperData
     */
     protected $_helperData;
	 
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    protected $resultPageFactory;
    protected $checkoutSession;
    protected $orderRepository;
    protected $_scopeConfig;
    protected $_orderFactory;
    protected $creditmemoSender;
    protected $orderSender;
    protected $urlBuilder;
	protected $_quoteFactory;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
        HelperData $helperData, 
        \Magetracer\Zottopay\Model\PaymentMethod $paymentMethod,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Quote\Model\QuoteFactory $_quoteFactory
    ) {
        $this->_helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
		$this->_quoteFactory = $_quoteFactory;
		
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_orderFactory = $orderFactory;
        $this->_paymentMethod = $paymentMethod;
        $this->creditmemoSender = $creditmemoSender;
        $this->orderSender = $orderSender;
    }


    
    public function execute()
    {
        
        //$this->_helperData->postLog(self::BrowserReturn, $_REQUEST);
		if(array_key_exists('order_id',$_REQUEST))
		{
			$order = $this->_orderFactory->create()->loadByIncrementId($_REQUEST['order_id']);
			$model = $this->_paymentMethod;      
			
			$quote = $this->_quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
			if ($quote->getId()) {
				$quote->setIsActive(1)->setReservedOrderId(null)->save();
				$this->checkoutSession->replaceQuote($quote);
				
				
				
			}
		}
		$this->messageManager->addWarningMessage('Payment Failed.');
		$url = 'checkout/cart';
        $url = $this->urlBuilder->getUrl($url);
        $this->_helperData->getParentLocationReplace($url);

       
    }


}


