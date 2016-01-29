<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

/**
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

        $this->_sitemapItems[] = new \Magento\Framework\DataObject(
            [
                'changefreq' => 'weekly',
                'priority' => '0.25',
                'collection' =>  \Magento\Framework\App\ObjectManager::getInstance()->create(
                        'Magefan\Blog\Model\Category'
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
                        'Magefan\Blog\Model\Post'
                    )->getCollection($this->getStoreId())
                    ->addStoreFilter($this->getStoreId())
                    ->addActiveFilter(),
            ]
        );
    }

}
