<?php
namespace Mconnect\Giftcard\Controller\Adminhtml\Giftcode;

use Mconnect\Giftcard\Model\GiftcodeFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Backend\App\Action
{
   
    protected $jsonFactory;

    protected $giftcodeFactory;
    
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        GiftcodeFactory $giftcode
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->giftcodeFactory = $giftcode;
    }
    
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $gcId) {
            $giftcode = $this->giftcodeFactory->create();
            $giftcode->load($gcId);
            try {
                $giftcode->setData('sender_name', $postItems[$gcId]['sender_name']);
                $giftcode->setData('recipient_name', $postItems[$gcId]['recipient_name']);
                $giftcode->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPageId($giftcode->getId(), $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPageId($giftcode->getId(), $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPageId(
                    $giftcode->getId(),
                    __('Something went wrong while saving the Gift Code.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
    
    protected function getErrorWithPageId($giftcode_id, $errorText)
    {
        return '[Gift Code ID: ' . $giftcode_id . '] ' . $errorText;
    }
}
