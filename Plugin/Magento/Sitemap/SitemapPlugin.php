<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Plugin\Magento\Sitemap;

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
     * Generated sitemaps
     * @var array
     */
    protected $generated = [];

    public function __construct(
        \Magefan\Blog\Model\SitemapFactory $sitemapFactory
    ) {
        $this->sitemapFactory = $sitemapFactory;
    }

    /**
     * Add magefan blog actions to allowed list
     * @param  \Magento\Sitemap\Model\Sitemap $sitemap
     * @return array
     */
    public function afterGenerateXml(\Magento\Sitemap\Model\Sitemap $sitemap, $result)
    {
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

        return $result;
    }
}
