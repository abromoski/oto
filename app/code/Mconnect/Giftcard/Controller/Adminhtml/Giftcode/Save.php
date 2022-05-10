<?php
namespace Mconnect\Giftcard\Controller\Adminhtml\Giftcode;

use Magento\Backend\App\Action;
use Mconnect\Giftcard\Model\GiftcodeFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $giftcodeFactory;
    
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Action\Context $context,
        GiftcodeFactory $giftcode
    ) {
        $this->giftcodeFactory = $giftcode;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mconnect_Giftcard::giftcode_save');
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
            if (isset($data['expiry_date'])) {
                $data['expiry_date'] = date('Y-m-d', strtotime($data['expiry_date']));
            }
            
            $model =  $this->giftcodeFactory->create();
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
                // @codingStandardsIgnoreLine
                $this->messageManager->addException(
                    $e,
                    __($e->getMessage().'Something went wrong while saving the page.')
                );
            }
            $this->_getSession()->setFormData($data);
            // @codingStandardsIgnoreLine
            return $resultRedirect->setPath(
                '*/giftcode/edit',
                ['giftcode_id' => $this->getRequest()->getParam('giftcode_id')]
            );
        }
        
        return $resultRedirect->setPath('*/giftcode/');
    }
}
