<?php
namespace Mconnect\Giftcard\Block\Adminhtml\Giftcode;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'giftcode_id';
        $this->_blockGroup = 'Mconnect_Giftcard';
        $this->_controller = 'adminhtml_giftcode';

        parent::_construct();

        if ($this->_isAllowedAction('Mconnect_Giftcard::giftcode_save')) {
            $this->buttonList->update('save', 'label', __('Save Gift Code'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Mconnect_Giftcard::giftcode_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Gift Code'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('giftcode')->getId()) {
            return __(
                "Edit Gift Code '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('giftcode')->getGiftcode())
            );
        } else {
            return __('New Gift Code');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'giftcard/giftcode/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";
        
        
        $this->_formScripts[] = "
						require([
							'jquery'    
						], function($){

							$('#edit_form').submit( function() {
								if ($('#edit_form').valid()) {
									$('#edit_form').append('<div class=\"loading-mask\" data-role=\"loader\" id=\"addrecordloader\"><div class=\"popup popup-loading\"><div class=\"popup-inner\">Please wait...</div></div></div>');
							
									$('#loading-mask').show();
								}
								return true;
							});

						});
					";
        return parent::_prepareLayout();
    }
}
