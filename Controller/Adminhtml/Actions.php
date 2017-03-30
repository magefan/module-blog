<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Abstract admin controller
 */
abstract class Actions extends \Magento\Backend\App\Action
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey;

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey;

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu;

    /**
     * Store config section key
     * @var string
     */
    protected $_configSection;

    /**
     * Request id key
     * @var string
     */
    protected $_idKey = 'id';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';

    /**
     * Save request params key
     * @var string
     */
    protected $_paramsHolder;

    /**
     * Model Object
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_model;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $_preparedActions = ['index', 'grid', 'new', 'edit', 'save', 'duplicate', 'delete', 'config', 'massStatus'];
        $_action = $this->getRequest()->getActionName();
        if (in_array($_action, $_preparedActions)) {
            $method = '_'.$_action.'Action';

            $this->_beforeAction();
            $this->$method();
            $this->_afterAction();
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
        $this->_setActiveMenu($this->_activeMenu);
        $title = __('Manage %1', $this->_getModel(false)->getOwnTitle(true));
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
     * New action
     * @return void
     */
    protected function _newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action
     * @return void
     */
    public function _editAction()
    {
        $model = $this->_getModel();

        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);

        $title = $model->getOwnTitle();

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit %1', $title);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New %1', $title);
            $breadcrumbLabel = __('Create %1', $title);
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $this->_getModelName($model) : __('New %1', $title)
        );

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData($this->_formSessionKey, true);
        if ($this->_paramsHolder) {
            $values = isset($values[$this->_paramsHolder]) ? $values[$this->_paramsHolder] : null;
        }

        if ($values) {
            $model->addData($values);
        }

        $this->_view->renderLayout();
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
     * Save action
     * @return void
     */
    public function _saveAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }
        $model = $this->_getModel();

        try {
            $params = $this->_paramsHolder ? $request->getParam($this->_paramsHolder) : $request->getParams();
            $idFieldName = $model->getResource()->getIdFieldName();
            if (isset($params[$idFieldName]) && empty($params[$idFieldName])) {
                unset($params[$idFieldName]);
            }
            $model->addData($params);

            $this->_beforeSave($model, $request);
            $model->save();
            $this->_afterSave($model, $request);

            $this->messageManager->addSuccess(__('%1 has been saved.', $model->getOwnTitle()));
            $this->_setFormData(false);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this %1. %2',
                    strtolower($model->getOwnTitle()),
                    $e->getMessage()
                )
            );
            $this->_setFormData();
        }

        $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        if ($request->getParam('isAjax')) {
            $block = $this->_objectManager->create('Magento\Framework\View\Layout')->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            $this->getResponse()->setBody(json_encode(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'model' => $model->toArray(),
                ]
            ));
        } else {
            if ($hasError || $request->getParam('back')) {
                $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
            } else {
                $this->_redirect('*/*');
            }
        }

    }

    /**
     * Duplicat action
     * @return void
     */
    protected function _duplicateAction()
    {
        try {
            $originModel = $this->_getModel();
            if (!$originModel->getId()) {
                throw new \Exception("Item is not longer exist.", 1);
            }

            $model = $originModel->duplicate();

            $this->messageManager->addSuccess(__('%1 has been duplicated.', $model->getOwnTitle()));
            $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
        } catch (Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this %1. %2',
                    strtolower($model->getOwnTitle()),
                    $e->getMessage()
                )
            );
            $this->_redirect('*/*/edit', [$this->_idKey => $originModel->getId()]);
        }
    }

    /**
     * Before model Save action
     * @return void
     */
    protected function _beforeSave($model, $request) {}

    /**
     * After model action
     * @return void
     */
    protected function _afterSave($model, $request) {}

    /**
     * Before action
     * @return void
     */
    protected function _beforeAction() {}

    /**
     * After action
     * @return void
     */
    protected function _afterAction() {}

    /**
     * Delete action
     * @return void
     */
    protected function _deleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $error = false;
        try {
            foreach($ids as $id) {
                $this->_objectManager->create($this->_modelClass)->setId($id)->delete();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException(
                $e,
                __("We can't delete %1 right now. %2",
                    strtolower($this->_getModel(false)->getOwnTitle()),
                    $e->getMessage()
                )
            );
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __('%1 have been deleted.', $this->_getModel(false)->getOwnTitle(count($ids) > 1))
            );
        }

        $this->_redirect('*/*');
    }

    /**
     * Change status action
     * @return void
     */
    protected function _massStatusAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $model = $this->_getModel(false);

        $error = false;

        try {

            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;

            if (is_null($status)) {
                throw new \Exception(__('Parameter "Status" missing in request data.'));
            }

            if (is_null($statusFieldName)) {
                throw new \Exception(__('Status Field Name is not specified.'));
            }

            foreach($ids as $id) {
                $this->_objectManager->create($this->_modelClass)
                    ->load($id)
                    ->setData($this->_statusField, $status)
                    ->save();
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException(
                $e,
                __("We can't change status of %1 right now. %2",
                    strtolower($model->getOwnTitle()),
                    $e->getMessage()
                )
            );
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __('%1 status have been changed.', $model->getOwnTitle(count($ids) > 1))
            );
        }

        $this->_redirect('*/*');

    }

    /**
     * Go to config section action
     * @return void
     */
    protected function _configAction()
    {
        $this->_redirect('admin/system_config/edit', ['section' => $this->_configSection()]);
    }

    /**
     * Set form data
     * @return $this
     */
    protected function _setFormData($data = null)
    {
        $this->_getSession()->setData($this->_formSessionKey,
            is_null($data) ? $this->getRequest()->getParams() : $data);

        return $this;
    }

    /**
     * Get core registry
     * @return void
     */
    protected function _getRegistry()
    {
        if (is_null($this->_coreRegistry)) {
            $this->_coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        }
        return $this->_coreRegistry;
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_allowedKey);
    }

    /**
     * Retrieve model object
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _getModel($load = true)
    {
        if (is_null($this->_model)) {
            $this->_model = $this->_objectManager->create($this->_modelClass);

            $id = (int)$this->getRequest()->getParam($this->_idKey);
            if ($id && $load) {
                $this->_model->load($id);
            }
        }
        return $this->_model;
    }

}
