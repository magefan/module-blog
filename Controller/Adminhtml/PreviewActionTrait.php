<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Trait for blog entity preview redirect action
 */
trait PreviewActionTrait
{
    /**
     * Redirect to entity preview url
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $model = $this->_getModel();
            if (!$model->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Item is not longer exist.'));
            }
            $previewUrl = $this->_objectManager->get(\Magefan\Blog\Model\PreviewUrl::class);

            $redirectUrl = $previewUrl->getUrl($model, $model->getControllerName());

            $this->getResponse()->setRedirect($redirectUrl);
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong %1', $e->getMessage())
            );
            $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
        }
    }
}
