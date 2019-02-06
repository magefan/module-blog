<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory as PostFactory;
use Magefan\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryFactory;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magefan\Blog\Model\Sitemap\SitepamManagent;

/**
 * Deprecated
 * Used for Magento 2.1.x only to create blog_sitemap.xml
 * Overide sitemap
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null,
        \Magento\Sitemap\Model\ItemProvider\ItemProviderInterface $itemProvider = null,
        \Magento\Sitemap\Model\SitemapConfigReaderInterface $configReader = null,
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $sitemapItemFactory = null,
        PostFactory $postCollectionFactory,
        CategoryFactory $categoryCollectionFactory,
        ProductMetadataInterface $megento,
        SitepamManagent $sitemapManagent
    )
    {
        parent::__construct($context, $registry, $escaper, $sitemapData, $filesystem, $categoryFactory, $productFactory, $cmsFactory, $modelDate, $storeManager, $request, $dateTime, $resource, $resourceCollection, $data, $documentRoot, $itemProvider, $configReader, $sitemapItemFactory);
        $this->postCollectionFactory = $postCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->megento = $megento;
        $this->sitemapManagent = $sitemapManagent;

    }


    /**
     * Initialize sitemap items
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();
        $this->_sitemapItems = [];


        $magentoVersion = $this->megento->getVersion();
        if ($magentoVersion == '2.3.0') {
            if ($this->sitemapManagent->isEnabledSitemap('index_page')) {
                $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                    [
                        'url' =>  '',
                        'updatedAt' => '2019-20-16',
                        'priority' =>  $this->sitemapManagent->getPriority('index_page'),
                        'changeFrequency' => $this->sitemapManagent->getFrequency('index_page'),
                    ]
                );
            }

            if ($this->sitemapManagent->isEnabledSitemap('categories_pages')) {
                $categories = $this->categoryCollectionFactory->create();
                foreach ($categories as $category) {
                    $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                        [
                            'url' => $category->getUrl(),
                            'updatedAt' => '2019-20-16',
                            'priority' => $this->sitemapManagent->getPriority('categories_pages'),
                            'changeFrequency' => $this->sitemapManagent->getFrequency('categories_pages'),

                        ]
                    );
                }
            }

            if ($this->sitemapManagent->isEnabledSitemap('posts_pages')) {
                $products = $this->postCollectionFactory->create();
                foreach ($products as $product) {
                    $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                        [
                            'url' => $product->getUrl(),
                            'updatedAt' => '2019-20-16',
                            'priority' => $this->sitemapManagent->getPriority('posts_pages'),
                            'changeFrequency' => 'dailly', //$this->sitemapManagent->getFrequency('posts_pages'),

                        ]
                    );
                }
            }
        } else {
            if ($this->sitemapManagent->isEnabledSitemap('categories_pages')) {
                $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                    [
                        'changefreq' => $this->sitemapManagent->getFrequency('categories_pages'),
                        'priority' => $this->sitemapManagent->getPriority('categories_pages'),
                        'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                            \Magefan\Blog\Model\Category::class
                        )->getCollection($this->getStoreId())
                            ->addStoreFilter($this->getStoreId())
                            ->addActiveFilter(),
                    ]
                );
            }
            if ($this->sitemapManagent->isEnabledSitemap('posts_pages')) {
                $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                    [
                        'changefreq' => $this->sitemapManagent->getFrequency('posts_pages'),
                        'priority' => $this->sitemapManagent->getPriority('posts_pages'),
                        'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                            \Magefan\Blog\Model\Post::class
                        )->getCollection($this->getStoreId())
                            ->addStoreFilter($this->getStoreId())
                            ->addActiveFilter(),
                    ]
                );
            }
        }
    }

    /**
     * Disable save action
     * @return self
     */
    public function save()
    {
        return $this;
    }
}
