<?php

namespace Etatvasoft\Banner\Block\Adminhtml\Banner\Edit\Tab;

/**
 * Class Main
 * @package Etatvasoft\Banner\Block\Adminhtml\Banner\Edit\Tab
 * Admin Banner edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
    
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Etatvasoft_Banner::banner');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Banner Information')]);

        $wysiwygConfig = $this->wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);

        if ($model->getBannerId()) {
            $fieldset->addField('banner_id', 'hidden', ['name' => 'banner_id']);
        }

        $fieldset->addField(
            'banner_title',
            'text',
            [
                'name' => 'post[banner_title]',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        )->setAfterElementHtml('
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    $( "#banner_title" ).attr( "class", "input-text admin__control-text validate-length maximum-length-30" );
                });
              });
       </script>
    ');

        if (is_array($model->getData('banner_image'))) {
            $model->setData('banner_image', $model->getData('banner_image')['value']);
        }
        $fieldset->addField(
            'banner_image',
            'image',
            [
                'title' => __('Image'),
                'label' => __('Image'),
                'name' => 'post[banner_image]',
                'required' => true,
                'value' => $model->getImagePath(),
                'note' => __('Note : Please upload image 1920 x 650(hight x width) size with jpg, jpeg, gif, png format'),
            ]
        )->setAfterElementHtml('
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    if($("#banner_image").attr("value")){
                        $("#banner_image").removeClass("required-file");
                    }else{
                        $("#banner_image").addClass("required-file");
                    }
                    $( "#banner_image" ).attr( "accept", "image/x-png,image/gif,image/jpeg,image/jpg,image/png" );
                });
              });
       </script>
    ');

        $fieldset->addField(
            'label_button_text',
            'text',
            [
                'name' => 'post[label_button_text]',
                'label' => __('Button Text'),
                'title' => __('Button Text'),
                'disabled' => $isElementDisabled
            ]
        )->setAfterElementHtml('
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    $( "#label_button_text" ).attr( "class", "input-text admin__control-text validate-length maximum-length-35" );
                });
              });
       </script>
    ');

        $fieldset->addField(
            'call_to_action',
            'text',
            [
                'name' => 'post[call_to_action]',
                'label' => __('Button Link'),
                'title' => __('Button Link'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'post[position]',
                'label' => __('Position'),
                'title' => __('Position'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('banner Status'),
                'name' => 'post[is_active]',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $contentField = $fieldset->addField(
            'banner_description',
            'editor',
            [
                'name' => 'post[banner_description]',
                'label' => __('Description'),
                'title' => __('Description'),
                'config' => $wysiwygConfig,
                'disabled' => $isElementDisabled,
                'validate-length' => true,
            ]
        )->setAfterElementHtml('
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    $( "#banner_description" ).attr( "class", "validate-length maximum-length-335" );
                });
              });
       </script>
    ');
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element'
        );
        $contentField->setRenderer($renderer);

        $this->_eventManager->dispatch('etatvasoft_banner_post_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Generate spaces
     * @param  int $n
     * @return string
     */
    protected function _getSpaces($n)
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }

        return $s;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Banner  Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Banner Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
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
}
