<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Observer;

use Magefan\Blog\Model\Config\Source\DesignVersion;
use Magento\Framework\Event\ObserverInterface;
use Magefan\Blog\Model\Config as BlogConfig;
use Magefan\Community\Api\HyvaThemeDetectionInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Filesystem\Driver\File;

class LayoutGenerateBlocksAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var BlogConfig
     */
    private $blogConfig;

    /**
     * @var HyvaThemeDetectionInterface
     */
    private $hyvaThemeDetection;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param BlogConfig $blogConfig
     * @param HyvaThemeDetectionInterface $hyvaThemeDetection
     * @param Repository $assetRepo
     * @param File $fileDriver
     */
    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        BlogConfig $blogConfig,
		HyvaThemeDetectionInterface $hyvaThemeDetection,
        Repository $assetRepo,
        File $fileDriver
    ) {
        $this->pageConfig = $pageConfig;
        $this->blogConfig = $blogConfig;
		$this->hyvaThemeDetection = $hyvaThemeDetection;
        $this->assetRepo = $assetRepo;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Add rel prev and rel next
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $availableActions = [
            'blog_archive_view',
            'blog_author_view',
            'blog_category_view',
            'blog_index_index',
            'blog_tag_view'
        ];
        $fan = $observer->getEvent()->getFullActionName();
        $this->applayNewDesignStyle($fan);
        if (!in_array($fan, $availableActions)) {
            return;
        }

        if ('blog_index_index' == $fan) {
            $displayMode = $this->blogConfig->getConfig(
                BlogConfig::XML_PATH_HOMEPAGE_DISPLAY_MODE
            );

            if (2 == $displayMode) {
                return;
            }
        }

        $productListBlock = $observer->getEvent()->getLayout()->getBlock('blog.posts.list');
        if (!$productListBlock) {
            return;
        }

        $toolbar = $productListBlock->getToolbarBlock();
        $toolbar->setCollection($productListBlock->getPostCollection());

        $pagerBlock = $toolbar->getPagerBlock();
        if (!($pagerBlock instanceof \Magento\Framework\DataObject)) {
            return;
        }

        if (1 < $pagerBlock->getCurrentPage()) {
            $this->pageConfig->addRemotePageAsset(
                $pagerBlock->getPageUrl(
                    $pagerBlock->getCollection()->getCurPage(-1)
                ),
                'link_rel',
                ['attributes' => ['rel' => 'prev']]
            );
        }
        if ($pagerBlock->getCurrentPage() < $pagerBlock->getLastPageNum()) {
            $this->pageConfig->addRemotePageAsset(
                $pagerBlock->getPageUrl(
                    $pagerBlock->getCollection()->getCurPage(+1)
                ),
                'link_rel',
                ['attributes' => ['rel' => 'next']]
            );
        }
    }


    private function applaynewDesignStyle($fan) {

        if ($this->blogConfig->getDesignVersion() != DesignVersion::VALOR ) {
            return;
        }

        if (!$this->hyvaThemeDetection->execute()) {
            if (in_array($fan, [
                'blog_archive_view',
                'blog_author_view',
                'blog_category_view',
                'blog_index_index',
                'blog_tag_view',
                'blog_search_index',
                'catalogsearch_result_index'
            ])) {
                $this->pageConfig->addPageAsset('Magefan_Blog::css/2025-07/style-blog-post-list.css');
            }

            if ('blog_author_view' == $fan) {
                try {
                    $template = 'Magefan_BlogExtra::css/2025-07/style-blog-author.css';
                    $asset = $this->assetRepo->createAsset($template);
                    $filePath = $asset->getSourceFile();
                    if ($this->fileDriver->isExists($filePath)) {
                        $this->pageConfig->addPageAsset($template);
                        $this->pageConfig->getAssetCollection()->remove('Magefan_Blog::css/2025-07/style-blog-post-list.css');
                    }
                } catch (\Exception $e) {
                    // do nothing
                }
            }
            
            if ('blog_post_view' == $fan) {
                $this->pageConfig->addPageAsset('Magefan_Blog::css/2025-07/style-blog-post.css');
                $this->pageConfig->getAssetCollection()->remove('Magefan_Blog::css/comments.css');
            }

            if ('catalog_product_view' == $fan  && $this->blogConfig->getConfig('mfblog/product_page/related_posts_enabled')) {
                $this->pageConfig->addPageAsset('Magefan_Blog::css/2025-07/style-product-page.css');
            }
        } elseif ($this->hyvaThemeDetection->execute() && strpos($fan, 'blog_') !== false) {
            $this->pageConfig->addPageAsset('Magefan_Blog::css/2025-07/style-variable.css');
        }
    }
}
