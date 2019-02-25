<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magefan\Blog\Api\SitemapConfigInterface;

/**
 * Deprecated
 * Used for Magento 2.1.x only to create blog_sitemap.xml
 * Overide sitemap
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * Initialize sitemap items
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sitemapConfig = $objectManager->get(SitemapConfigInterface::class);

        $sitemapItems = [];
        if ($sitemapConfig->isEnabledSitemap(SitemapConfigInterface::HOME_PAGE)) {
            $sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $sitemapConfig->getFrequency(SitemapConfigInterface::HOME_PAGE),
                    'priority' => $sitemapConfig->getPriority(SitemapConfigInterface::HOME_PAGE),
                    'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magento\Framework\Data\Collection::class
                    )->addItem(
                        \Magento\Framework\App\ObjectManager::getInstance()->create(
                            \Magento\Framework\DataObject::class
                        )->setData([
                            'updated_at' => '',
                            'url' => $objectManager->get(\Magefan\Blog\Model\Url::class)->getBasePath(),
                        ])
                    )
                ]
            );
        }

        if ($sitemapConfig->isEnabledSitemap(SitemapConfigInterface::CATEGORIES_PAGE)) {
            $sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $sitemapConfig->getFrequency(SitemapConfigInterface::CATEGORIES_PAGE),
                    'priority' => $sitemapConfig->getPriority(SitemapConfigInterface::CATEGORIES_PAGE),
                    'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magefan\Blog\Model\Category::class
                    )->getCollection($this->getStoreId())
                        ->addStoreFilter($this->getStoreId())
                        ->addActiveFilter(),
                ]
            );
        }

        if ($sitemapConfig->isEnabledSitemap(SitemapConfigInterface::POSTS_PAGE)) {
            $sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $sitemapConfig->getFrequency(SitemapConfigInterface::POSTS_PAGE),
                    'priority' => $sitemapConfig->getPriority(SitemapConfigInterface::POSTS_PAGE),
                    'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magefan\Blog\Model\Post::class
                    )->getCollection($this->getStoreId())
                        ->addStoreFilter($this->getStoreId())
                        ->addActiveFilter(),
                ]
            );
        }

        $productMetadata = $objectManager->get(ProductMetadataInterface::class);
        if (version_compare($productMetadata->getVersion(), '2.3.0', '<')) {
            $this->_sitemapItems = $sitemapItems;
        } else {
            $this->_sitemapItems = [];
            foreach ($sitemapItems as $sitemapItem) {
                foreach ($sitemapItem->getCollection() as $item) {
                    $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                        [
                            'url' => $item->getUrl(),
                            'updated_at' => $item->getData('update_time'),
                            'priority' => $sitemapItem->getData('priority'),
                            'change_frequency' =>  $sitemapItem->getData('changefreq'),
                        ]
                    );
                }
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
