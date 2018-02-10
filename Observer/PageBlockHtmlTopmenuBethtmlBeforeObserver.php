<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Blog observer
 */
class PageBlockHtmlTopmenuBethtmlBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magefan\Blog\Helper\Menu
     */
    protected $menuHelper;

    /**
     * @param \Magefan\Blog\Helper\Menu $menuHelper
     */
    public function __construct(
        \Magefan\Blog\Helper\Menu $menuHelper
    ) {
        $this->menuHelper = $menuHelper;
    }

    /**
     * Page block html topmenu gethtml before
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getMenu();
        $tree = $menu->getTree();

        $blogNode = $this->menuHelper->getBlogNode($menu, $menu->getTree());
        if ($blogNode) {
            $menu->addChild($blogNode);
        }
    }
}
