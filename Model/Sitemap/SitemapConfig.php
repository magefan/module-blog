<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Sitemap;

use Magefan\Blog\Api\SitemapConfigInterface;

class SitemapConfig implements SitemapConfigInterface
{

    /**
     * @param $page
     * @return bool
     */
    public function isEnabledSitemap($page)
    {
        return true;
    }

    /**
     * @param $page
     * @return string
     */
    public function getFrequency($page)
    {
        switch ($page) {
            case 'index':
                $frequency = 'Dailly';
                break;
            case 'category':
                $frequency = 'Dailly';
                break;
            case 'post':
                $frequency = 'Dailly';
                break;
            default:
                $frequency = 'Dailly';
        }
        return $frequency;
    }

    /**
     * @param $page
     * @return float
     */
    public function getPriority($page)
    {

        switch ($page) {
            case 'index':
                $priority = 1;
                break;
            case 'category':
                $priority = 0.75;
                break;
            case 'post':
                $priority = 0.5;
                break;
            default:
                $priority = 0.25;
        }
        return $priority;
    }
}