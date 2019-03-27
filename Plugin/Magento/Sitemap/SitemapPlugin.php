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

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var mixed
     */
    protected $config;

    /**
     * Generated sitemaps
     * @var array
     */
    protected $generated = [];

    /**
     * SitemapPlugin constructor.
     * @param \Magefan\Blog\Model\SitemapFactory $sitemapFactory
     * @param CategoryFactory $categoryFactory
     * @param PostFactory $postFactory
     * @param null|\Magefan\Blog\Model\Config config
     */
    public function __construct(
        \Magefan\Blog\Model\SitemapFactory $sitemapFactory,
        CategoryFactory $categoryFactory,
        PostFactory $postFactory,
        $config = null
    ) {
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->sitemapFactory = $sitemapFactory;

        $this->config = $config ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magefan\Blog\Model\Config::class);
    }

    /**
     * Deprecated
     * Used for Magento 2.1.x only to create blog_sitemap.xml
     * Add magefan blog actions to allowed list
     * @param  \Magento\Framework\Model\AbstractModel $sitemap
     * @return array
     */
    public function afterGenerateXml(\Magento\Framework\Model\AbstractModel $sitemap, $result)
    {
        if ($this->isEnabled($sitemap)) {
            /* if ($this->isMageWorxXmlSitemap($sitemap) || !method_exists($sitemap, 'collectSitemapItems')) { */
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
            /* } */
        }
        return $result;
    }

    /**
     * Deprecated
     * @param \Magento\Framework\Model\AbstractModel $sitemap
     * @param $result
     * @return mixed
     */
    public function afterCollectSitemapItems(\Magento\Framework\Model\AbstractModel $sitemap, $result)
    {
        return $result;
        /*
        if ($this->isEnabled($sitemap) && !$this->isMageWorxXmlSitemap($sitemap)) {
            $storeId = $sitemap->getStoreId();

            $sitemap->addSitemapItem(new DataObject(
                [
                    'changefreq' => 'weekly',
                    'priority' => '0.25',
                    'collection' => $this->categoryFactory->create()
                        ->getCollection($storeId)
                        ->addStoreFilter($storeId)
                        ->addActiveFilter(),
                ]
            ));

            $sitemap->addSitemapItem(new DataObject(
                [
                    'changefreq' => 'weekly',
                    'priority' => '0.25',
                    'collection' => $this->postFactory->create()
                        ->getCollection($storeId)
                        ->addStoreFilter($storeId)
                        ->addActiveFilter(),
                ]
            ));
        }

        return $result;
        */
    }

    /**
     * @param $sitemap
     * @return mixed
     */
    protected function isEnabled($sitemap)
    {
        return $this->config->isEnabled(
            $sitemap->getStoreId()
        );
    }

    /**
     * Deprecated
     * @param $sitemap
     * @return mixed
     */
    public function isMageWorxXmlSitemap($sitemap)
    {
        return (get_class($sitemap) == 'MageWorx\XmlSitemap\Model\Rewrite\Sitemap\Interceptor');
    }
}
