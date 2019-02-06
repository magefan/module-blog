<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magefan\Blog\Model\Sitemap\SitepamManagent;

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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $megento = $objectManager->create(ProductMetadataInterface::class);
        $sitemapManagent = $objectManager->create(SitepamManagent::class);


        parent::_initSitemapItems();

        $sitemapItems = [];
        if ($sitemapManagent->isEnabledSitemap('index_page')) {
            $sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $sitemapManagent->getFrequency('index_page'),
                    'priority' => $sitemapManagent->getPriority('index_page'),
                    'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magento\Framework\Data\Collection::class
                    )->addItem(
                        \Magento\Framework\App\ObjectManager::getInstance()->create(
                            \Magento\Framework\DataObject::class
                        )->setData([
                            'updatedAt' => '2019-20-16',
                            'url' => '',
                        ])
                    )
                ]
            );
        }

        if ($sitemapManagent->isEnabledSitemap('categories_pages')) {
            $sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $sitemapManagent->getFrequency('categories_pages'),
                    'priority' => $sitemapManagent->getPriority('categories_pages'),
                    'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magefan\Blog\Model\Category::class
                    )->getCollection($this->getStoreId())
                        ->addStoreFilter($this->getStoreId())
                        ->addActiveFilter(),
                ]
            );
        }

        if ($sitemapManagent->isEnabledSitemap('posts_pages')) {
            $sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $sitemapManagent->getFrequency('posts_pages'),
                    'priority' => $sitemapManagent->getPriority('posts_pages'),
                    'collection' => \Magento\Framework\App\ObjectManager::getInstance()->create(
                        \Magefan\Blog\Model\Post::class
                    )->getCollection($this->getStoreId())
                        ->addStoreFilter($this->getStoreId())
                        ->addActiveFilter(),
                ]
            );
        }

        if (version_compare($megento->getVersion(), '2.3.0', '<')) {
            $this->_sitemapItems = $sitemapItems;
        } else {
            $this->_sitemapItems = [];
            foreach ($sitemapItems as $sitemapItem) {
                foreach ($sitemapItem->getCollection() as $item) {
                    $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                        [
                            'url' => $item->getUrl(),
                            'updated_at' => '',
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
