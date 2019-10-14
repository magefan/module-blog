<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Infortis\UltraMegamenu;

use Magento\Store\Model\ScopeInterface;
use Magefan\Blog\Model\Config;

/**
 * Blog Smartwave Megamenu Block
 */
class Topmenu extends \Magento\Framework\View\Element\Text
{
    /**
     * @var \Magefan\Blog\Helper\Menu
     */
    protected $menuHelper;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magefan\Blog\Helper\Menu               $menuHelper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magefan\Blog\Helper\Menu $menuHelper,
        array $data = []
    ) {
        $this->menuHelper = $menuHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $blogNode = $this->menuHelper->getBlogNode();

        $html = '';
        if ($blogNode) {
            $max_level = $this->_scopeConfig->getValue(
                Config::XML_PATH_TOP_MENU_MAX_DEPTH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $children = $blogNode->getChildren();
            $hasChildren = $children->count();
            $active = ($this->getRequest()->getModuleName() == 'blog');

            $html .= '
            <li class="nav-item level0 level-top classic ' . ($active ? 'active' : '') . ' ' . ($hasChildren ? 'parent nav-item--only-subcategories' : '') . ' ">
                <a class="level-top" 
                   href="' . $blogNode->getUrl() . '" 
                   title="' . $this->escapeHtml($blogNode->getName()) . '">
                    <span>' . $this->escapeHtml($blogNode->getName()) . '</span>';
            if ($hasChildren) {
                $html .= '<span class="caret"></span>';
            }
            $html .= '</a>';
            if ($hasChildren) {
                $html .= '<span class="opener"></span>';
            }

            if ($hasChildren) {
                $html .= $this->getSubmenuItemsHtml($children, 1, $max_level);
            }

            $html .= '</li>';
        }

        return $html;
    }

    public function getSubmenuItemsHtml($children, $level = 1, $max_level = 0)
    {
        $html = '';

        if (!$max_level || $max_level >= $level) {
            $html .= '<ul class="level0 nav-submenu nav-panel--dropdown nav-panel">';
            foreach ($children as $child) {
                $subChildren = $child->getChildren();
                $html .= '<li class="nav-item level' . $level . ' classic">
                            <a href="' . $child->getUrl() . '">
                                <span>' . $this->escapeHtml($child->getName()) . '</span>
                            </a>
                            ' . ($subChildren->count() ?
                            $this->getSubmenuItemsHtml($subChildren, $level+1, $max_level) :
                            '') . '
                           </li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }
}
