<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magefan\Blog\Api\AuthorCollectionInterfaceFactory as CollectionFactory;
use Magefan\Blog\Api\SitemapConfigInterface;
use Magento\Framework\Module\Manager;

class Author implements ItemProviderInterface
{
    /**
     * Sitemap config
     *
     * @var SitemapConfigInterface
     */
    private $sitemapConfig;

    /**
     * Blog tag collection factory
     *
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Sitemap item factory
     *
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param SitemapConfigInterface $sitemapConfig
     * @param CollectionFactory $collectionFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param Manager $moduleManager
     */
    public function __construct(
        SitemapConfigInterface $sitemapConfig,
        CollectionFactory $collectionFactory,
        SitemapItemInterfaceFactory $itemFactory,
        Manager $moduleManager
    ) {
        $this->sitemapConfig = $sitemapConfig;
        $this->collectionFactory = $collectionFactory;
        $this->itemFactory = $itemFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        if (!$this->sitemapConfig->isEnabledSitemap(SitemapConfigInterface::AUTHOR_PAGE, $storeId)) {
            return [];
        }

        if ($this->moduleManager->isEnabled('Magefan_BlogAuthor')) {
            $collection = $this->collectionFactory->create()
                ->addStoreFilter($storeId)
                ->addActiveFilter()
                ->getItems();
        } else {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->getItems();
        }

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'priority' => $this->sitemapConfig->getPriority(SitemapConfigInterface::AUTHOR_PAGE, $storeId),
                'changeFrequency' => $this->sitemapConfig->getFrequency(SitemapConfigInterface::AUTHOR_PAGE, $storeId),
            ]);
        }, $collection);

        return $items;
    }
}
