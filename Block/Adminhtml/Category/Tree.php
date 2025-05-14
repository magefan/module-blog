<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\Auth\Session;
use Magefan\Blog\Model\Category;
use Magefan\Blog\Model\CategoryFactory;
use Magefan\Blog\Model\ResourceModel\Category\Collection;
use Magefan\Blog\Model\ResourceModel\Category\Tree as CategoryTree;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\Store;
use Magefan\Blog\Model\ResourceModel\Category\CollectionFactory;


class Tree extends AbstractCategory
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/category/tree.phtml';

    /**
     * @var Session
     */
    protected $_backendSession;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @param Context $context
     * @param CategoryTree $categoryTree
     * @param Registry $registry
     * @param CategoryFactory $categoryFactory
     * @param EncoderInterface $jsonEncoder
     * @param Helper $resourceHelper
     * @param Session $backendSession
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context             $context,
        CategoryTree        $categoryTree,
        Registry            $registry,
        CategoryFactory     $categoryFactory,
        CollectionFactory $collectionFactory,
        EncoderInterface    $jsonEncoder,
        Helper              $resourceHelper,
        Session             $backendSession,
        array               $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_resourceHelper = $resourceHelper;
        $this->_backendSession = $backendSession;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $collectionFactory, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(0);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $addUrl = $this->getUrl("*/*/new", ['_current' => false, 'id' => null, '_query' => false]);
        if ($this->getStore()->getId() == Store::DEFAULT_STORE_ID) {
            $this->addChild(
                'add_sub_button',
                Button::class,
                [
                    'label' => __('Add Subcategory'),
                    'onclick' => "addNew('" . $addUrl . "', false)",
                    'class' => 'add',
                    'id' => 'add_subcategory_button',
                    'style' => $this->canAddSubCategory() ? '' : 'display: none;'
                ]
            );

            if ($this->canAddRootCategory()) {
                $this->addChild(
                    'add_root_button',
                    Button::class,
                    [
                        'label' => __('Add Root Category'),
                        'onclick' => "addNew('" . $addUrl . "', true)",
                        'class' => 'add',
                        'id' => 'add_root_category_button'
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }



    /**
     * Get add root button html
     *
     * @return string
     */
    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }

    /**
     * Get add sub button html
     *
     * @return string
     */
    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    /**
     * Get expand button html
     *
     * @return string
     */
    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }

    /**
     * Get collapse button html
     *
     * @return string
     */
    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }

    /**
     * Get store switcher
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Get loader tree url
     *
     * @param bool|null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        $params = ['_current' => true, 'id' => null, 'store' => null];
        if ($expanded === null && $this->_backendSession->getIsTreeWasExpanded() || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/categoriesJson', $params);
    }

    /**
     * Get nodes url
     *
     * @return string
     */
    public function getNodesUrl()
    {
        return $this->getUrl('blog/category/tree');
    }

    /**
     * Get switcher tree url
     *
     * @return string
     */
    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            'blog/category/tree',
            ['_current' => true, 'store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * Get is was expanded
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsWasExpanded()
    {
        return $this->_backendSession->getIsTreeWasExpanded();
    }

    /**
     * Get move url
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('blog/category/move', ['store' => $this->getRequest()->getParam('store')]);
    }

    /**
     * Get tree
     *
     * @param mixed|null $parenNodeCategory
     * @return array
     */
    public function getTree($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $tree = $rootArray['children'] ?? [];

        return $tree;
    }

    /**
     * Get tree json
     *
     * @param mixed|null $parenNodeCategory
     * @return string
     */
    public function getTreeJson($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $json = $this->_jsonEncoder->encode($rootArray['children'] ?? []);

        return $json;
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->buildNodeName($node);

        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id'] = $node->getId();
        $item['store'] = (int)$this->getStore()->getId();
        $item['path'] = $node->getData('path');
        $item['a_attr'] = ['class' => $node->getIsActive() ? 'active-category' : 'not-active-category'];
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');

        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store

        $item['allowDrag'] = $allowMove && ($node->getLevel() == 1 && $rootForStores ? false : true);

        $isParent = $this->_isParentSelectedCategory($node);

        $item['children'] = [];

        if ($node->hasChildren()) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 1) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Get category name
     *
     * @param DataObject $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getTitle());
        $result .= ' (ID: ' . $node->getId() . ')';

        if ($this->_withProductCount) {
            $result .= ' (' . $node->getPostCount() . ')';
        }
        return $result;
    }

    /**
     * Is category movable
     *
     * @param Node|array $node
     * @return bool
     */
    protected function _isCategoryMoveable($node)
    {
        $options = new DataObject(['is_moveable' => true, 'category' => $node]);

        $this->_eventManager->dispatch('adminhtml_blog_category_tree_is_moveable', ['options' => $options]);

        return $options->getIsMoveable();
    }

    /**
     * Is parent selected category
     *
     * @param Node|array $node
     * @return bool
     */
    protected function _isParentSelectedCategory($node)
    {
        if ($node && $this->getCategory()) {
            $pathIds = $this->getCategory()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if page loaded by outside link to category edit
     *
     * @return boolean
     */
    public function isClearEdit()
    {
        return (bool)$this->getRequest()->getParam('clear');
    }

    /**
     * Check availability of adding root category
     *
     * @return boolean
     */
    public function canAddRootCategory()
    {
        $options = new DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_blog_category_tree_can_add_root_category',
            ['category' => $this->getCategory(), 'options' => $options, 'store' => $this->getStore()->getId()]
        );

        return $options->getIsAllow();
    }

    /**
     * Check availability of adding sub category
     *
     * @return boolean
     */
    public function canAddSubCategory()
    {
        $options = new DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_blog_category_tree_can_add_sub_category',
            ['category' => $this->getCategory(), 'options' => $options, 'store' => $this->getStore()->getId()]
        );

        return $options->getIsAllow();
    }
}
