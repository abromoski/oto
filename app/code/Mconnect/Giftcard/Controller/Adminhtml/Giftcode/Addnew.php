<?php
namespace Mconnect\Giftcard\Controller\Adminhtml\Giftcode;

use Magento\Backend\App\Action;
use Mconnect\Giftcard\Model\GiftcodeFactory;

class Addnew extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $_giftcodeFactory;
    private $_giftcodeCollectionFactory;
    
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Action\Context $context,
        GiftcodeFactory $giftcode,
        \Mconnect\Giftcard\Model\ResourceModel\Giftcode\CollectionFactory $giftcodeCollectionFactory
    ) {
        $this->_giftcodeFactory = $giftcode;
        $this->_giftcodeCollectionFactory = $giftcodeCollectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mconnect_Giftcode::add_new');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id = $this->getRequest()->getParam('giftcode_id');
        
         /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            $product_id = $this->getRequest()->getParam('product_id');

            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
            $code_pattern   = $product->getGcCodePattern();
            
            do {
                $giftcode = $this->generateGiftcode($code_pattern);
            } while ($this->isDuplicateCode($giftcode));
            
            $data['giftcode'] = $giftcode;
            $data['status'] = \Mconnect\Giftcard\Model\Giftcode::STATUS_ACTIVE;
            $data['current_balance'] = $data['initial_value'];
            $data['increment_id'] = 'By Admin';
            
            if (isset($data['expiry_date'])) {
                $data['expiry_date'] = date('Y-m-d', strtotime($data['expiry_date']));
            }
            
            $model =  $this->_giftcodeFactory->create();
            $model->setData($data)
                ->setId($this->getRequest()->getParam('giftcode_id'));
            
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Gift code has successfully saved'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/giftcode/edit',
                        ['giftcode_id' => $model->getId(), '_current' => true]
                    );
                }
                $this->_redirect('*/giftcode/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __($e->getMessage().'Something went wrong while saving the page.')
                );
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/giftcode/edit',
                ['giftcode_id' => $this->getRequest()->getParam('giftcode_id')]
            );
        }
        
        return $resultRedirect->setPath('*/giftcode/');
    }
    
    public function generateGiftcode($pattern)
    {
        $nums   = "0123456789";
        $chars  = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        
        $slug_arr = preg_split('/( }{|}|{| )/', $pattern);
        
        $res = "";
        foreach ($slug_arr as $slug) {
            $slug_val = $slug;
            
            if ($slug == 'L' || $slug == 'l') {
                $slug_val = $nums[mt_rand(0, strlen($nums)-1)];
            }
            if ($slug == 'D' || $slug == 'd') {
                $slug_val = $chars[mt_rand(0, strlen($chars)-1)];
            }
            $res .= $slug_val;
        }
        return $res;
    }
    
    public function isDuplicateCode($giftcode)
    {
        $gccollection = $this->_giftcodeCollectionFactory->create();
        $gccollection->addFieldToFilter('giftcode', ['eq' => $giftcode]);
        
        if (count($gccollection)) {
            return true;
        }
        return false;
    }
}
