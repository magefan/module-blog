<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Sidebar;

use \Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Module\Manager;

class PostRelatedProducts extends AbstractProduct implements IdentityInterface
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'post_related_products';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_itemCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * Related products block construct
     * @param Context $context
     * @param Visibility $catalogProductVisibility
     * @param Manager $moduleManager
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
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
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $post = $this->getPost();

        $this->_itemCollection = $post->getRelatedProducts(false)
            ->addAttributeToSelect('required_options');

        if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }

        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_itemCollection->setPageSize(
            (int) $this->_scopeConfig->getValue(
                'mfblog/sidebar/'.$this->_widgetKey.'/number_of_products',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );

        $this->_itemCollection->getSelect()->order('rl.position', 'ASC');

        $this->_eventManager->dispatch('mfblog_relatedproducts_block_load_collection_before', [
            'block' => $this,
            'collection' => $this->_itemCollection
        ]);

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Return products collection instance
     *
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
        if (!$this->getPost()) {
            return [];
        }

        $identities = [];
        foreach ($this->getItems() as $item) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $identities = array_merge($identities, $item->getIdentities());
        }

        return $identities;
    }

    /**
     * Synonim to getItems. Added to support different templates
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllItems()
    {
        return $this->getItems();
    }

    /**
     * Synonim to getItems. Added to support different templates
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItemCollection()
    {
        return $this->getItems();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function toHtml()
    {
        if (!$this->getPost()) {
            return '';
        }

        return parent::toHtml();
    }
}
