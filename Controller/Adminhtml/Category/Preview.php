<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Category;

/**
 * Blog post preview controller
 */
class Preview extends \Magefan\Blog\Controller\Adminhtml\Category
{
    /**
     * Execute action
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $post = $this->_getModel();
            if (!$post->getId()) {
                throw new \LocalizedException("Item is not longer exist.", 1);
            }

            $previewUrl = $this->_objectManager->get(\Magefan\Blog\Model\PreviewUrl::class);
            $redirectUrl = $previewUrl->getUrl(
                $post,
                $previewUrl::CONTROLLER_CATEGORY
            );
            $this->getResponse()->setRedirect($redirectUrl);
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong %1', $e->getMessage())
            );
            $this->_redirect('*/*/edit', [$this->_idKey => $post->getId()]);
        }
    }
}
