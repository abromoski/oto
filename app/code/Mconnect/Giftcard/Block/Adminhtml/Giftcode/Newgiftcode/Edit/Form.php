<?php
namespace Mconnect\Giftcard\Block\Adminhtml\Giftcode\Newgiftcode\Edit;

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
        
        $product_options = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $productCollection->addAttributeToSelect("*");
        $productCollection->addFilter('type_id', 'giftcardproduct');
        $productCollection->addFieldToFilter('status', 1);

        $product_options[''] = __('Please select product');
        
        foreach ($productCollection as $gc_product) {
            $product_options[$gc_product->getId()] = $gc_product->getName();
        }
        
        $emails  = $this->getEmailTemplates();
        $encoder = $objectManager->create('\Magento\Framework\Json\Encoder');
        $js_data = $encoder->encode($emails);
        
        $product_field = $fieldset->addField(
            'product_id',
            'select',
            [
                'label' => __('Giftcard Product'),
                'title' => __('Giftcard Product'),
                'name' => 'product_id',
                'required' => true,
                'options' => $product_options,
                'disabled' => $isElementDisabled
            ]
        );
        
        $product_field->setAfterElementHtml(
            "<script type='text/javascript'>
						require([
							'jquery'    
						], function($){
							var edata = ".$js_data.";
							
							$('#product_id').change( function() {
								product_id = $(this).val();
								var p_emails = edata[product_id];
								var templates = {};
								
								for (var key in p_emails) {
									if (p_emails.hasOwnProperty(key)) {
										templates[key] = p_emails[key];
									}
								}
								
								var el = $('#email_template');
								el.empty();
								el.append($('<option></option>').attr('value', '').text('".__("Please select email")."'));
								$.each(templates, function(key,value) {
									el.append($('<option></option>')
										.attr('value', key).text(value));
								});
							});
						});
				</script>"
        );
        
        $fieldset->addField(
            'initial_value',
            'text',
            [
                'label' => __('Initial Value'),
                'title' => __('Initial Value'),
                'name' => 'initial_value',
                'required' => true,
                'class' => 'validate-greater-than-zero',
                'disabled' => $isElementDisabled
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
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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
        
        $email_templates  = ['' => __("Please select email")];
        
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
        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    protected function getEmailTemplates()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_gcEmails = $objectManager->create('Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email');
        
        $gc_emails = $_gcEmails->loadEmailData();
        
        $emails = [];
        foreach ($gc_emails as $email) {
            $template = $objectManager->create('\Magento\Email\Model\Template');
            $template->load($email['email_template']);
            
            if ($email['email_template'] !== "giftcard_email_giftcard_template") {
                $emails[$email['product_id']][$email['gemail_id']] = $template->getTemplateCode();
            } else {
                $emailConfig = $objectManager->create('\Magento\Email\Model\Template\Config');
                $templateId = 'giftcard_email_giftcard_template';
                $templateLabel = $emailConfig->getTemplateLabel($templateId);
                $templateLabel = __('%1 (Default)', $templateLabel);
                
                $emails[$email['product_id']][$email['gemail_id']] = $templateLabel;
            }
        }
        return $emails;
    }
}
