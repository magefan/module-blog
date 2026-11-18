<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Search;

/**
 * Blog search results view
 */
class Index extends \Magefan\Blog\App\Action\Action
{
    /**
     * View blog search results action
     *
     * @return \Magento\Framework\Controller\ResultInterface|null
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            $this->_forwardNoroute();
            return;
        }

        $resultPage = $this->_objectManager->get(\Magefan\Blog\Helper\Page::class)
            ->prepareResultPage($this, new \Magento\Framework\DataObject());
        return $resultPage;
    }
}
