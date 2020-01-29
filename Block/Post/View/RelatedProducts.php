<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;
use \Magento\Catalog\Block\Product\AbstractProduct;
use \Magento\Framework\DataObject\IdentityInterface;

/**
 * Blog post related products block
 */
class RelatedProducts extends AbstractProduct implements IdentityInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_itemCollection;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * Related products block construct
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }

    /**
     * Premare block data
     * @return $this
     */
    protected function _prepareCollection()
    {
        $post = $this->getPost();

        $this->_itemCollection = $post->getRelatedProducts()
            ->addAttributeToSelect('required_options');

        if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }

        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->setPageSize(
            (int) $this->_scopeConfig->getValue(
                \Magefan\Blog\Model\Config::XML_RELATED_PRODUCTS_NUMBER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );

        $this->_itemCollection->getSelect()->order('rl.position', 'ASC');

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Retrieve true if Display Related Products enabled
     * @return boolean
     */
    public function displayProducts()
    {
        return (bool) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_PRODUCTS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItems()
    {
        if (null === $this->_itemCollection) {
            $this->_prepareCollection();
        }
        return $this->_itemCollection;
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData(
                'post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

     /**
      * Return identifiers for produced content
      *
      * @return array
      */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }

        return $identities;
    }

     /**
      * Return blog type. Can be related-rule, related, upsell-rule, upsell, crosssell-rule, crosssell
      *
      * @return string
      */
    public function getType()
    {
        if ($this->getData('related_products_type')) {
            return $this->getData('related_products_type');
        }

        return 'related-rule';
    }

    /**
     * Synonim to getItems. Added to support different templates
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllItems()
    {
        return $this->getItems();
    }

    /**
     * Synonim to getItems. Added to support different templates
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItemCollection()
    {
        return $this->getItems();
    }

    /**
     * @return int
     */
    public function hasItems()
    {
        return count($this->getItems());
    }

    /**
     * @return bool
     */
    public function isShuffled()
    {
        if ($this->getData('is_shuffled')) {
            return (bool)$this->getData('is_shuffled');
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canItemsAddToCart()
    {
        if ($this->getData('can_items_add_to_cart')) {
            return (bool)$this->getData('can_items_add_to_cart');
        }
        return false;
    }

    /**
     * Return blog html
     * @return bool
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html = str_replace('product-item" style="display: none;"', 'product-item"', $html);

        return $html;
    }
}
