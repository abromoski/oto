<?php
namespace Mconnect\Giftcard\Block\Adminhtml\Giftcode\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('giftcode');
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setUseContainer(true);
        
        if ($this->_isAllowedAction('Mconnect_Giftcard::giftcode_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        $fieldset = $form->addFieldset('gifcode_base_fieldset', ['legend' => __('Gift Code Information')]);
        
        //--------------------------------------
        if ($model->getId()) {
            $fieldset->addField('giftcode_id', 'hidden', ['name' => 'giftcode_id']);
        }
        
        $fieldset->addField(
            'giftcode',
            'label',
            [
                'label' => __('Gift Code'),
                'title' => __('Gift Code')
            ]
        );
        
        $fieldset->addField(
            'initial_value',
            'label',
            [
                'label' => __('Initial Value'),
                'title' => __('Initial Value')
            ]
        );
        
        $fieldset->addField(
            'current_balance',
            'label',
            [
                'label' => __('Current Balance'),
                'title' => __('Current Balance')
            ]
        );
        
        $fieldset->addField(
            'status',
            'label',
            [
                'label' => __('Status'),
                'title' => __('Status')
            ]
        );
        
        $fieldset->addField(
            'expiry_date',
            'date',
            [
                'name' => 'expiry_date',
                'label' => __('Expiration Date'),
                'title' => __('Expiration Date'),
                'disabled' => $isElementDisabled,
                'class' => '',
                'singleClick'=> true,
                'date_format' => 'MM/dd/yyyy',
                'time'=>false
            ]
        );
        
        $fieldset->addField(
            'created_at',
            'label',
            [
                'label' => __('Created At'),
                'title' => __('Created At')
            ]
        );
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objstore_option = $objectManager->create('Magento\Store\Ui\Component\Listing\Column\Store\Options');
        $store_options = $objstore_option->toOptionArray();
        
        $fieldset->addField(
            'store_id',
            'select',
            [
                'label' => __('Website'),
                'title' => __('Website'),
                'name' => 'store_id',
                'required' => true,
                'values' => $store_options,
                'disabled' => $isElementDisabled
            ]
        );
        
        //--------------------------------------
        
        $fieldset1 = $form->addFieldset('gifcode_sender_fieldset', ['legend' => __('Sender Information')]);
        
        $fieldset1->addField(
            'sender_name',
            'text',
            [
                'name' => 'sender_name',
                'label' => __('sender Name'),
                'title' => __('sender Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset1->addField(
            'sender_email',
            'text',
            [
                'name' => 'sender_email',
                'label' => __('sender Email'),
                'title' => __('sender Email'),
                'required' => true,
                'class' => 'validate-email',
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset1->addField(
            'headline',
            'text',
            [
                'name' => 'headline',
                'label' => __('Headline'),
                'title' => __('Headline'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
        
        $helper = $objectManager->create('Mconnect\Giftcard\Helper\Data');
        $max_length = $helper->getMsgMaxLength();
        $txt_class = "validate-length maximum-length-".$max_length;
        
        $fieldset1->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Message'),
                'title' => __('Message'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'class' => $txt_class
            ]
        );
        
        $email_templates  = $this->getEmailTemplates($model->getProductId(), $model->getStoreId());
        $fieldset1->addField(
            'email_template',
            'select',
            [
                'label' => __('Email Template'),
                'title' => __('Email Template'),
                'name' => 'email_template',
                'required' => true,
                'options' => $email_templates,
                'disabled' => $isElementDisabled
            ]
        );
        
        $image_path = '';
        $src = '';
        if ($model->getId()) {
            $image_path = $model->getImagePath();
            $src = ($image_path) ? 'catalog/product'.$image_path : '';
        }
        if ($model->getId()) {
            $custom_image_path = $model->getCustomImagePath();
            $src = ($custom_image_path) ? 'catalog/product/customgiftcard'.$custom_image_path : '';
        }
        
        $fieldset1->addType('template_image', '\Mconnect\Giftcard\Block\Adminhtml\Giftcode\Edit\Renderer\Image');
        $fieldset1->addField(
            'template_image',
            'template_image',
            [
                'label' => __('Image'),
                'title' => __('Image'),
                'src' => $src
            ]
        );
        
        //--------------------------------------
        $fieldset2 = $form->addFieldset('gifcode_recipient_fieldset', ['legend' => __('Recipient Information')]);
        
        $fieldset2->addField(
            'recipient_name',
            'text',
            [
                'name' => 'recipient_name',
                'label' => __('Recipient Name'),
                'title' => __('Recipient Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset2->addField(
            'recipient_email',
            'text',
            [
                'name' => 'recipient_email',
                'label' => __('Recipient Email'),
                'title' => __('Recipient Email'),
                'required' => true,
                'class' => 'validate-email',
                'disabled' => $isElementDisabled
            ]
        );
        //--------------------------------------
        $fieldset3 = $form->addFieldset('gifcode_history_fieldset', ['legend' => __('History')]);
        
        $giftcode_data = $model->getData();
        
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        
        if (isset($giftcode_data['initial_value'])) {
            $giftcode_data['initial_value'] = $priceHelper->currency($giftcode_data['initial_value'], true, false);
        }
        
        if (isset($giftcode_data['current_balance'])) {
            $giftcode_data['current_balance'] = $priceHelper->currency($giftcode_data['current_balance'], true, false);
        }
        
        if (isset($giftcode_data['status'])) {
            $statuses = $model->getAvailableStatuses();
            $giftcode_data['status'] = $statuses[$giftcode_data['status']];
        }
        $form->setValues($giftcode_data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    protected function getEmailTemplates($product_id, $store_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_gcEmails = $objectManager->create('Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email');
        
        $gc_emails = $_gcEmails->loadEmailData($product_id, $store_id);
        
        $emails = [];
        foreach ($gc_emails as $email) {
            $template = $objectManager->create('\Magento\Email\Model\Template');
            $template->load($email['email_template']);
            
            if ($email['email_template'] !== "giftcard_email_giftcard_template") {
                $emails[$email['gemail_id']] = $template->getTemplateCode();
            } else {
                $emailConfig = $objectManager->create('\Magento\Email\Model\Template\Config');
                $templateId = 'giftcard_email_giftcard_template';
                $templateLabel = $emailConfig->getTemplateLabel($templateId);
                $templateLabel = __('%1 (Default)', $templateLabel);
                
                $emails[$email['gemail_id']] = $templateLabel;
            }
        }
        return $emails;
    }
}
