<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magefan\Blog\Api\SitemapConfigInterface;
use Magento\Store\Model\ScopeInterface;

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

            $url = $objectManager->get(\Magefan\Blog\Model\Url::class)->getBasePath();

            $scopeConfig = $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
            $advancedPermalinkEnabled =  $scopeConfig->getValue(
                Config::XML_PATH_ADVANCED_PERMALINK_ENABLED,
                ScopeInterface::SCOPE_STORE
            );

            if (!$advancedPermalinkEnabled) {
                $redirectToNoSlash = $scopeConfig->getValue(
                    Config::XML_PATH_REDIRECT_TO_NO_SLASH,
                    ScopeInterface::SCOPE_STORE
                );

                if (!$redirectToNoSlash) {
                    $url = trim($url, '/') . '/';
                }
            }

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
                            'url' => $url,
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
        $version = str_replace(['dev-', '-develop'], ['', '.0'], $productMetadata->getVersion());
        if (version_compare($version, '2.3.0', '<')) {
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

    /**
     * Fix for MageWorx_XmlSitemap
     * @return string
     */
    public function getSitemapPath(): string
    {
        $path = $this->getData('sitemap_path');
        if ($serverPath = $this->getServerPath()) {
            if (!$this->_directory->isDirectory($serverPath)) {
                $serverPath = BP . '/' . $serverPath;
            }

            return rtrim($serverPath, '/') . $path;
        }

        return $path;
    }
}
