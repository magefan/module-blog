<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Blog home page view
 */
class Index extends \Magefan\Blog\App\Action\Action
{
    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            //return $this->_forwardNoroute();
            return $this->_objectManager->get(ResultFactory::class)
                ->create(ResultFactory::TYPE_FORWARD)
                ->forward('noroute');
        }

        $resultPage = $this->_objectManager->get(\Magefan\Blog\Helper\Page::class)
            ->prepareResultPage($this, new \Magento\Framework\DataObject());
        return $resultPage;
    }
}
