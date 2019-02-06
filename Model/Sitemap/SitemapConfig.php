<?php
/**
 * Created by PhpStorm.
 * User: dev4
 * Date: 06.02.19
 * Time: 9:52
 */

namespace Magefan\Blog\Model\Sitemap;


class SitemapConfig implements SitepamManagent
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
            case 'index_page':
                $frequency = 'Dailly';
                break;
            case 'categories_pages':
                $frequency = 'Dailly';
                break;
            case 'posts_pages':
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
            case 'index_page':
                $priority = 1;
                break;
            case 'categories_pages':
                $priority = 0.75;
                break;
            case 'posts_pages':
                $priority = 0.5;
                break;
            default:
                $priority = 0.25;
        }
        return $priority;
    }
}