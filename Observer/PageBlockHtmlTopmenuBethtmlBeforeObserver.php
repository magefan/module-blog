<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\ScopeInterface;
use Magefan\Blog\Helper\Config;

/**
 * Blog observer
 */
class PageBlockHtmlTopmenuBethtmlBeforeObserver implements ObserverInterface
{
    /**
     * Show top menu item config path
     */
    const XML_PATH_TOP_MENU_SHOW_ITEM = 'mfblog/top_menu/show_item';

    /**
     * Top menu item text config path
     */
    const XML_PATH_TOP_MENU_ITEM_TEXT = 'mfblog/top_menu/item_text';

    /**
     * Top menu include categories config path
     */
    const XML_PATH_TOP_MENU_INCLUDE_CATEGORIES = 'mfblog/top_menu/include_categories';

    /**
     * Top menu max depth config path
     */
    const XML_PATH_TOP_MENU_MAX_DEPTH = 'mfblog/top_menu/max_depth';

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @param \Magento\Framework\Registry $registry,
     */
    protected $registry;

    /**
     * @param \Magefan\Blog\Model\Url                                      $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magefan\Blog\Model\Url $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->_url = $url;
        $this->_scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
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
        if (!$this->_scopeConfig->isSetFlag(static::XML_PATH_TOP_MENU_SHOW_ITEM, ScopeInterface::SCOPE_STORE)) {
            return;
        }

        if (!$this->_scopeConfig->isSetFlag(Config::XML_PATH_EXTENSION_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return;
        }

        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getMenu();
        $block = $observer->getBlock();
        $tree = $menu->getTree();
        $addedNodes = [];

        $data = [
            'name'      => $this->_scopeConfig->getValue(static::XML_PATH_TOP_MENU_ITEM_TEXT, ScopeInterface::SCOPE_STORE),
            'id'        => 'magefan-blog',
            'url'       => $this->_url->getBaseUrl(),
            'is_active' => ($block->getRequest()->getModuleName() == 'blog')
        ];

        $addedNodes[0] = new Node($data, 'id', $tree, $menu);
        $menu->addChild($addedNodes[0]);

        $includeCategories = $this->_scopeConfig->getValue(
            static::XML_PATH_TOP_MENU_INCLUDE_CATEGORIES, ScopeInterface::SCOPE_STORE
        );

        if ($includeCategories) {

            $maxDepth = $this->_scopeConfig->getValue(
                static::XML_PATH_TOP_MENU_MAX_DEPTH, ScopeInterface::SCOPE_STORE
            );

            $items = $this->getGroupedChilds();
            $currentCategoryId = $this->getCurrentCategory()
                ? $this->getCurrentCategory()->getId()
                : 0;

            foreach ($items as $item) {

                $parentId = (int) $item->getParentId();

                if (!isset($addedNodes[$parentId])) {
                    continue;
                }

                if ($maxDepth > 0  &&  $item->getLevel() >= $maxDepth) {
                    continue;
                }

                $data = [
                    'name'      => $item->getTitle(),
                    'id'        => 'magefan-blog-category-' . $item->getId(),
                    'url'       => $item->getCategoryUrl(),
                    'is_active' => $currentCategoryId == $item->getId()
                ];

                $addedNodes[$item->getId()] = new Node($data, 'id', $tree, $menu);
                $addedNodes[$parentId]->addChild(
                    $addedNodes[$item->getId()]
                );
            }
        }
    }

    /**
     * Retrieve sorted array of categories
     * @return array
     */
    protected function getGroupedChilds()
    {
        return $this->categoryCollectionFactory->create()
            ->addActiveFilter()
            ->addFieldToFilter('include_in_menu', 1)
            ->setOrder('position')
            ->getTreeOrderedArray();
    }

    /**
     * Retrieve current blog category
     * @return \Magefan\Blog\Model\Category | null
     */
    protected function getCurrentCategory()
    {
        return $this->registry->registry('current_blog_category');
    }
}
