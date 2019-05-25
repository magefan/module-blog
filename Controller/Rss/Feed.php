<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Rss;

/**
 * Blog rss feed view
 */
class Feed extends \Magefan\Blog\App\Action\Action
{
    /**
     * View blog rss feed action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }

        $this->_view->loadLayout();
        $this->getResponse()
            ->setHeader('Content-type', 'text/xml; charset=UTF-8')
            ->setBody(
                $this->_view->getLayout()->getBlock('blog.rss.feed')->toHtml()
            );
    }
}
