<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Api;

/**
 * Interface SitemapConfigInterface
 */
interface SitemapConfigInterface
{
    public const HOME_PAGE = 'index';
    public const CATEGORIES_PAGE = 'category';
    public const POSTS_PAGE = 'post';
    public const TAGS_PAGE = 'tag';
    public const AUTHOR_PAGE = 'author';

    /**
     * Check if the sitemap is enabled
     *
     * @param string $page
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledSitemap($page, $storeId = null): bool;

    /**
     * Get sitemap change frequency
     *
     * @param string $page
     * @param int|null $storeId
     * @return string
     */
    public function getFrequency($page, $storeId = null): string;

    /**
     * Get sitemap priority
     *
     * @param string $page
     * @param int|null $storeId
     * @return float
     */
    public function getPriority($page, $storeId = null): float;
}
