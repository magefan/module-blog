<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Plugin\Magento\Sitemap;

use Magento\Framework\DataObject;
use Magefan\Blog\Model\Category;
use Magefan\Blog\Model\Post;
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

    /**
     * @var
     */
    protected $category;

    /**
     * @var Post
     */
    protected $post;

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
        Category $category,
        Post $post
    ) {
        $this->post = $post;
        $this->category = $category;
        $this->sitemapFactory = $sitemapFactory;
    }

    /**
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
                'collection' =>  $this->category
                    ->getCollection($storeId)
                    ->addStoreFilter($storeId)
                    ->addActiveFilter(),
            ]
        ));

        $sitemap->addSitemapItem(new DataObject(
            [
                'changefreq' => 'weekly',
                'priority' => '0.25',
                'collection' =>  $this->post
                    ->getCollection($storeId)
                    ->addStoreFilter($storeId)
                    ->addActiveFilter(),
            ]
        ));

        return $result;
    }
}
