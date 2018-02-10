<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

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
        $this->_sitemapItems = [];

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

    /**
     * Disable save action
     * @return self
     */
    public function save()
    {
        return $this;
    }
}
