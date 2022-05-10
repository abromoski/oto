<?php 
namespace Magetracer\Zottopay\Controller\Payment; 


use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magetracer\Zottopay\Helper\Data as HelperData;

class Ipn extends \Magento\Framework\App\Action\Action
{

    const BrowserReturn = "[IPN Return]";
	
	/**
     * @var HelperData
     */
    protected $_helperData;
	
   /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $jsonResultFactory;
	
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

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
		HelperData $helperData, 
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magetracer\Zottopay\Model\PaymentMethod $paymentMethod,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
		$this->_helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
		$this->jsonResultFactory = $jsonResultFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderFactory = $orderFactory;
        $this->_paymentMethod = $paymentMethod;
        $this->creditmemoSender = $creditmemoSender;
        $this->orderSender = $orderSender;
    }


    protected function _createInvoice($order)
    {
        if (!$order->canInvoice()) {
            return;
        }
        
        $invoice = $order->prepareInvoice();
        if (!$invoice->getTotalQty()) {
            throw new \RuntimeException("Cannot create an invoice without products.");
        }

        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $order->addRelatedObject($invoice);
    }

    public function execute()
    {
		$raw_post = file_get_contents("php://input" , true);
		
		$rawData = json_decode($raw_post);
		//$this->_helperData->postLog(self::BrowserReturn, $rawData);
		// $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/custom.log');
		// $logger = new \Zend\Log\Logger();
		// $logger->addWriter($writer);
		
		//$logger->info($rawData);
		//$logger->info(print_r($rawData, true));
		//$logger->info($rawData['order_id']);
		//$logger->info($rawData['trans_id']);
		// $status = $rawData['status'];
		// $order_id = $rawData['order_id'];
		// $transaction_id = $rawData['trans_id'];
		 $status = $rawData->status;
       $order_id = $rawData->order_id;
       $transaction_id = $rawData->trans_id;
	   $logger->info($rawData->order_id);
	   $logger->info($rawData->trans_id);
		 $logger->info($rawData->status); 		
			$order = $this->_orderFactory->create()->loadByIncrementId($order_id);
			$model = $this->_paymentMethod;    
		
			if($order)
			{
				 if($status == "success" || $status == 1){
						$order->getPayment()->setLastTransId($transaction_id);
						$order->setState($model->getConfigData('success_order_status'));
						$order->setStatus($model->getConfigData('success_order_status'));
						$order->addStatusToHistory($model->getConfigData('success_order_status'), __(self::BrowserReturn.'Order Complete via IPN transaction_id = '.$transaction_id));
						$this->orderSender->send($order);
						if ($model->getConfigData('invoice')){
							$this->_createInvoice($order);
						}
						$order->save();
				}elseif ($status == 2) {
						$order->setState('pending');
						$order->setStatus('pending');
						$order->addStatusToHistory('pending', __(self::BrowserReturn.'Order pending via IPN transaction_id = '.$transaction_id));
						$order->save();
						
				} else {
						$order->setState($model->getConfigData('failure_order_status'));
						$order->setStatus($model->getConfigData('failure_order_status'));
						$order->addStatusToHistory($model->getConfigData('failure_order_status'), __(self::BrowserReturn.'Order failed via IPN transaction_id = '.$transaction_id));
						$order->save();
						
				}
			}
		
    }


}


