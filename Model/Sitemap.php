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
        \Magento\Framework\App\ProductMetadataInterface $megento
    )
    {
        parent::__construct($context, $registry, $escaper, $sitemapData, $filesystem, $categoryFactory, $productFactory, $cmsFactory, $modelDate, $storeManager, $request, $dateTime, $resource, $resourceCollection, $data, $documentRoot, $itemProvider, $configReader, $sitemapItemFactory);
        $this->postCollectionFactory = $postCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->megento = $megento;

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
            $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'url' =>  $magentoVersion,
                    'changefreq' => 'dayily',
                    'priority' => '1'
                ]
            );


            $categories = $this->categoryCollectionFactory->create();
            foreach ($categories as $category) {
                $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                    [
                        'url' =>  $category->getUrl(),
                        'changeFrequency' => 'weekly',
                        'priority' => '0.25',
                    ]
                );
            }

            $products = $this->postCollectionFactory->create();
            foreach ($products as $product) {
                $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                    [
                        'url' => $product->getUrl(),
                        'changeFrequency' => 'weekly',
                        'priority' => '0.25',
                    ]
                );
            }
        } else {
            $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => 'weekly',
                    'priority' => '0.25',
                    'collection' =>  \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magefan\Blog\Model\Category::class
                    )->getCollection($this->getStoreId())
                        ->addStoreFilter($this->getStoreId())
                        ->addActiveFilter(),
                ]
            );

            $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => 'weekly',
                    'priority' => '0.25',
                    'collection' =>  \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magefan\Blog\Model\Post::class
                    )->getCollection($this->getStoreId())
                        ->addStoreFilter($this->getStoreId())
                        ->addActiveFilter(),
                ]
            );
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
