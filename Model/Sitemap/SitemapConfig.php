<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Sitemap;

use Magefan\Blog\Api\SitemapConfigInterface;

/**
 * Class Sitemap Config Model
 */
class SitemapConfig implements SitemapConfigInterface
{
    /**
     * @param string $page
     * @return bool
     */
    public function isEnabledSitemap($page)
    {
        return true;
    }

    /**
     * @param string $page
     * @return string
     */
    public function getFrequency($page)
    {
        switch ($page) {
            case 'index':
                $frequency = 'daily';
                break;
            case 'category':
                $frequency = 'daily';
                break;
            case 'post':
                $frequency = 'daily';
                break;
            default:
                $frequency = 'daily';
        }
        return $frequency;
    }

    /**
     * @param string $page
     * @return float
     */
    public function getPriority($page)
    {
        switch ($page) {
            case 'index':
                $priority = 1;
                break;
            case 'category':
                $priority = 0.8;
                break;
            case 'post':
                $priority = 0.5;
                break;
            default:
                $priority = 0.3;
        }
        return $priority;
    }
}
