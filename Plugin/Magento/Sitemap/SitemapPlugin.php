<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Plugin\Magento\Sitemap;

use Magefan\Blog\Model\CategoryFactory;
use Magefan\Blog\Model\PostFactory;
use Magento\Framework\DataObject;
use Magento\Sitemap\Model\Sitemap;

/**
 * Plugin for sitemap generation
 */
class SitemapPlugin
{
    /**
     * @var \Magefan\Blog\Model\SitemapFactory
     */
    protected $sitemapFactory;


    protected $categoryFactory;


    protected $postFactory;

    /**
     * Generated sitemaps
     * @var array
     */
    protected $generated = [];

    /**
     * SitemapPlugin constructor.
     * @param \Magefan\Blog\Model\SitemapFactory $sitemapFactory
     * @param Category $category
     * @param Post $post
     */
    public function __construct(
        \Magefan\Blog\Model\SitemapFactory $sitemapFactory,
        CategoryFactory $categoryFactory,
        PostFactory $postFactory
    ) {
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->sitemapFactory = $sitemapFactory;
    }

    /**
     * Deprecated
     * Used for Magento 2.1.x only to create blog_sitemap.xml
     * Add magefan blog actions to allowed list
     * @param  \Magento\Sitemap\Model\Sitemap $sitemap
     * @return array
     */
    public function afterGenerateXml(Sitemap $sitemap, $result)
    {
        if (!method_exists($sitemap, 'collectSitemapItems')) {

            $sitemapId = $sitemap->getId() ?: 0;
            if (in_array($sitemapId, $this->generated)) {
                return $result;
            }
            $this->generated[] = $sitemapId;

            $blogSitemap = $this->sitemapFactory->create();
            $blogSitemap->setData(
                $sitemap->getData()
            );

            $blogSitemap->setSitemapFilename(
                'blog_' . $sitemap->getSitemapFilename()
            );

            $blogSitemap->generateXml();

        }

        return $result;
    }

    /**
     * @param Sitemap $sitemap
     * @param $result
     * @return mixed
     */
    public function afterCollectSitemapItems(Sitemap $sitemap, $result) {

        $storeId = $sitemap->getStoreId();

        $sitemap->addSitemapItem(new DataObject(
            [
                'changefreq' => 'weekly',
                'priority' => '0.25',
                'collection' =>  $this->categoryFactory->create()
                    ->getCollection($storeId)
                    ->addStoreFilter($storeId)
                    ->addActiveFilter(),
            ]
        ));

        $sitemap->addSitemapItem(new DataObject(
            [
                'changefreq' => 'weekly',
                'priority' => '0.25',
                'collection' =>  $this->postFactory->create()
                    ->getCollection($storeId)
                    ->addStoreFilter($storeId)
                    ->addActiveFilter(),
            ]
        ));

        return $result;
    }
}
