<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Post\View;

use Magefan\Blog\Model\TemplatePool;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Module\Manager;
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
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magefan\Blog\Model\TemplatePool
     */
    protected $templatePool;

    /**
     * @var \Magefan\Community\Api\HyvaThemeDetectionInterface
     */
    protected $mfHyvaThemeDetection;

    /**
     * Related products block construct
     * @param Context $context
     * @param Visibility $catalogProductVisibility
     * @param Manager $moduleManager
     * @param CollectionFactory $productCollectionFactory
     * @param TemplatePool $templatePool
     * @param \Magefan\Community\Api\HyvaThemeDetectionInterface $mfHyvaThemeDetection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magefan\Blog\Model\TemplatePool $templatePool,
        \Magefan\Community\Api\HyvaThemeDetectionInterface $mfHyvaThemeDetection,
        array $data = []
    ) {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_moduleManager = $moduleManager;
        $this->templatePool = $templatePool;
        $this->mfHyvaThemeDetection = $mfHyvaThemeDetection;
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

        $this->_itemCollection = $post->getRelatedProducts()
            ->addAttributeToSelect('required_options');

        if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }

        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->setPageSize($this->getPageSize());

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
     * Retrieve true if Display Related Products enabled
     *
     * @return boolean
     */
    public function displayProducts(): bool
    {
        return (bool) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_PRODUCTS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the collection of items.
     *
     * @return \Magento\Framework\Data\Collection|null
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
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
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
     * Check if there are items
     *
     * @return int
     */
    public function hasItems(): int
    {
        return count($this->getItems());
    }

    /**
     * Checks if the data is marked as shuffled.
     *
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
     * Determines whether items can be added to the cart.
     *
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
     *
     * @return bool
     */
    protected function _toHtml()
    {
        if (!$this->displayProducts()) {
            return '';
        }

        $this->prepareHyvaSliderData();

        $html = parent::_toHtml();
        $html = str_replace('product-item" style="display: none;"', 'product-item"', $html);

        return $html;
    }

    /**
     * Pass page_size and additional_filters to Hyvä product-slider.phtml via getData().
     *
     * @return void
     */
    protected function prepareHyvaSliderData(): void
    {
        if (!$this->mfHyvaThemeDetection->execute()
            || $this->getTemplate() !== 'Magento_Catalog::product/slider/product-slider.phtml'
        ) {
            return;
        }

        if (!$this->hasData('page_size')) {
            $this->setData('page_size', $this->getPageSize());
        }
        if (!$this->hasData('additional_filters')) {
            $this->setData('additional_filters', $this->getAdditionalFilters());
        }
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        $templateName = $this->getData('template_type') ?: (string)$this->_scopeConfig->getValue(
            'mfblog/post_view/related_products/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($template = $this->templatePool->getTemplate('blog_post_view_related_product', $templateName)) {
            $this->_template = $template;
        }
        return parent::getTemplate();
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return (int) $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::XML_RELATED_PRODUCTS_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return additional filters to restrict the product collection.
     *
     * @return array
     */
    public function getAdditionalFilters(): array
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = (int)$item->getId();
        }

        // Prevent Hyvä slider from fetching all products when no related products are set
        return [
            [
                'field'         => 'entity_id',
                'value'         => empty($ids) ? [0] : $ids,
                'conditionType' => 'in',
            ],
        ];
    }
}