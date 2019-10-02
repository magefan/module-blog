<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Smartwave\Megamenu;

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

        $menu_type = $this->getData('menu_type');
        $sw_menu_cat_columns = $this->getData('sw_menu_cat_columns') ?: 4;
        $sw_menu_static_width = $this->getData('sw_menu_static_width');
        $max_level = $this->_scopeConfig->getValue(
            Config::XML_PATH_TOP_MENU_MAX_DEPTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $custom_style = '';
        if ($menu_type == "staticwidth") {
            $custom_style = ' style="width: 500px;"';
        }

        if ($menu_type == "staticwidth" && $sw_menu_static_width) {
            $custom_style = ' style="width: '.$sw_menu_static_width.';"';
        }

        $html = '';
        if ($blogNode) {
            $children = $blogNode->getChildren();
            $hasChildren = $children->count();

            $html .= '
                <li class="ui-menu-item level0 ' . $menu_type . ' '  . ($hasChildren ? 'parent' : '')  . '">';
            if ($hasChildren) {
                $html .= '<div class="open-children-toggle"></div>';
            }
                $html .= '<a href="' . $blogNode->getUrl() . '" class="level-top">
                            <span>' . $this->escapeHtml($blogNode->getName()) . '</span>
                          </a>';

            if ($hasChildren) {
                $html .= '<div class="level0 submenu"'.$custom_style.'>';
                    $html .= '<div class="row">';
                        $html .= $this->getSubmenuItemsHtml(
                            $children,
                            1,
                            $max_level,
                            12,
                            $menu_type,
                            $sw_menu_cat_columns
                        );
                    $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</li>';
        }

        return $html;
    }

    public function getSubmenuItemsHtml(
        $children,
        $level = 1,
        $max_level = 0,
        $column_width = 12,
        $menu_type = 'fullwidth',
        $columns = null
    ) {
        $html = '';

        if (!$max_level ||
            ($max_level && $max_level == 0) ||
            ($max_level && $max_level > 0 && $max_level-1 >= $level)
        ) {
            $column_class = "";
            if ($level == 1 && $columns && ($menu_type == 'fullwidth' || $menu_type == 'staticwidth')) {
                $column_class = "col-sm-".$column_width." ";
                $column_class .= "mega-columns columns".$columns;
            }
            $html = '<ul class="subchildmenu '.$column_class.'">';
            foreach ($children as $child) {
                $sub_children = $child->getChildren();
                $hasSubChildren = $sub_children->count();

                $item_class = 'level'.$level.' ';
                if ($hasSubChildren) {
                    $item_class .= 'parent ';
                }
                $html .= '<li class="ui-menu-item '.$item_class.'">';
                if ($hasSubChildren) {
                    $html .= '<div class="open-children-toggle"></div>';
                }

                $html .= '<a href="'.$child->getUrl().'">';
                $html .= '<span>'.$child->getName();
                $html .= '</span></a>';
                if ($hasSubChildren) {
                    $html .= $this->getSubmenuItemsHtml($sub_children, $level+1, $max_level, $column_width, $menu_type);
                }
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        return $html;
    }
}
