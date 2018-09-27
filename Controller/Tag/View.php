<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Tag;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog tag posts view
 */
class View extends \Magefan\Blog\App\Action\Action
{
    /**
     * View blog author action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }

        $tag = $this->_initTag();
        if (!$tag) {
            return $this->_forwardNoroute();
        }

        $this->_objectManager->get(\Magento\Framework\Registry::class)->register('current_blog_tag', $tag);

        $resultPage = $this->_objectManager->get(\Magefan\Blog\Helper\Page::class)
            ->prepareResultPage($this, $tag);

        return $resultPage;
    }

    /**
     * Init author
     *
     * @return \Magefan\Blog\Model\Tag || false
     */
    protected function _initTag()
    {
        $id = $this->getRequest()->getParam('id');

        $tag = $this->_objectManager->create(\Magefan\Blog\Model\Tag::class)->load($id);

        if (!$tag->isActive()) {
            return false;
        }

        return $tag;
    }
}
