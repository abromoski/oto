<?php

namespace Etatvasoft\Banner\Controller\Adminhtml;

/**
 * Class Actions
 * @package Etatvasoft\Banner\Controller\Adminhtml
 * Abstract admin controller
 */
abstract class Actions extends \Magento\Backend\App\Action
{
    /**
     * Form session key
     * @var string
     */
    protected $formSessionKey;

    /**
     * Allowed Key
     * @var string
     */
    protected $allowedKey;

    /**
     * Model class name
     * @var string
     */
    protected $modelClass;

    /**
     * Active menu key
     * @var string
     */
    protected $activeMenu;

    /**
     * Store config section key
     * @var string
     */
    protected $configSection;

    /**
     * Request id key
     * @var string
     */
    protected $idKey = 'banner_id';

    /**
     * Save request params key
     * @var string
     */
    protected $paramsHolder;

    /**
     * Model Object
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $model;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $_preparedActions = ['index', 'grid', 'new', 'edit', 'save', 'delete', 'massStatus'];
        $_action = $this->getRequest()->getActionName();
        if (in_array($_action, $_preparedActions)) {
            $method = '_'.$_action.'Action';
            $this->_beforeAction();
            $this->$method();
        }
    }

    /**
     * Index action
     * @return void
     */
    protected function _indexAction()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->activeMenu);
        $title = __('Manage Banners');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    /**
     * Grid action
     * @return void
     */
    protected function _gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
    /**
     * new action
     * @return void
     */
    protected function _newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save action
     * @return void
     */
    protected function _saveAction()
    {
        $request = $this->getRequest();
        $model = $this->_getModel();

        try {
            $params = $this->paramsHolder ? $request->getParam($this->paramsHolder) : $request->getParams();
            $model->addData($params);
            $this->_beforeSave($model, $request);
            if ($request->getParam('banner_id') == null) {
                if (array_key_exists('banner_image', $model->getData())) {
                    $model->save();
                    $this->messageManager->addSuccess(__($model->getOwnTitle().' has been saved.'));
                }
            } else {
                $model->save();
                $this->messageManager->addSuccess(__($model->getOwnTitle().' has been saved.'));
            }
            $this->_setFormData(false);

            if ($request->getParam('back')) {
                $this->_redirect('*/*/edit', [$this->idKey => $model->getId()]);
            } else {
                $this->_redirect('*/*');
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving this '.strtolower($model->getOwnTitle()).'.').' '.$e->getMessage());
            $this->_setFormData();
        }

        $this->_redirect('*/*/edit', [$this->idKey => $model->getId()]);
    }

    /**
     * edit action
     * @return void
     */
    protected function _editAction()
    {
        $model = $this->_getModel();

        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->activeMenu);

        $title = $model->getOwnTitle();

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit '.$title);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New '.$title);
            $breadcrumbLabel = __('Create '.$title);
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $this->_getModelName($model) : __('New '.$title)
        );

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData($this->formSessionKey, true);
        if ($this->paramsHolder) {
            $values = isset($values[$this->paramsHolder]) ? $values[$this->paramsHolder] : null;
        }
        if ($values) {
            $model->addData($values);
        }

        $this->_view->renderLayout();
    }

    /**
     * Before action
     * @return void
     */
    protected function _beforeAction()
    {
    }

    /**
     * Before saveaction
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
    }

    /**
     * Set form data
     * @return $this
     */
    protected function _setFormData($data = null)
    {
        $this->_getSession()->setData(
            $this->formSessionKey,
            ($data == null) ? $this->getRequest()->getParams() : $data
        );

        return $this;
    }

    /**
     * Get core registry
     * @return void
     */
    protected function _getRegistry()
    {
        if ($this->coreRegistry == null) {
            $this->coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        }
        return $this->coreRegistry;
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->allowedKey);
    }

    /**
     * Retrieve model name
     * @param  boolean $plural
     * @return string
     */
    protected function _getModelName(\Magento\Framework\Model\AbstractModel $model)
    {
        return $model->getName() ?: $model->getTitle();
    }

    /**
     * Retrieve model object
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _getModel($load = true)
    {

        if ($this->model == null) {
            $this->model = $this->_objectManager->create($this->modelClass);

            $id = (int)$this->getRequest()->getParam($this->idKey);

            if ($id && $load) {
                $this->model->load($id);
            }
        }
        return $this->model;
    }

    /**
     * Change status action
     * @return void
     */
    protected function _massStatusAction()
    {
        $ids = $this->getRequest()->getParam($this->idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $model = $this->_getModel(false);

        $error = false;

        try {
            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->statusField;

            if ($status == null) {
                $this->messageManager->addError('Parameter Status missing in request data.');
            }

            if ($statusFieldName == null) {
                $this->messageManager->addError('Status field name is not specified.');
            }

            foreach ($ids as $id) {
                $this->_objectManager->create($this->modelClass)
                    ->load($id)
                    ->setData($this->statusField, $status)
                    ->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t change status of '.strtolower($model->getOwnTitle()).' right now. '.$e->getMessage()));
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __($model->getOwnTitle(count($ids) > 1).' status have been changed.')
            );
        }

        $this->_redirect('*/*');
    }

    /**
     * Delete action
     * @return void
     */
    protected function _deleteAction()
    {
        $ids = $this->getRequest()->getParam($this->idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $error = false;
        try {
            foreach ($ids as $id) {
                $this->_objectManager->create($this->modelClass)->setId($id)->delete();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t delete '.strtolower($this->_getModel(false)->getOwnTitle()).' right now. '.$e->getMessage()));
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __($this->_getModel(false)->getOwnTitle(count($ids) > 1).' have been deleted.')
            );
        }

        $this->_redirect('*/*');
    }
}
