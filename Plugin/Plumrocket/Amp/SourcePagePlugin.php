<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Plugin\Plumrocket\Amp;

/**
 * Plugin for source page (amp extension by Plumrocket)
 */
class SourcePagePlugin
{
    /**
     * Add magefan blog pages to soruce
     * @param  \Plumrocket\Amp\Model\System\Config\Source\Page $page
     * @param  array $pages
     * @return array
     */
    public function afterToArray(\Plumrocket\Amp\Model\System\Config\Source\Page $page, $pages)
    {
        $pages['magefan_blog_index_index'] = __('Blog Main Page');
        $pages['magefan_blog_post_view'] = __('Blog Post Pages');
        $pages['magefan_blog_category_view'] = __('Blog Category Pages');
        $pages['magefan_blog_category_view'] = __('Blog Category Pages');
        $pages['magefan_blog_archive_view'] = __('Blog Archive Pages');
        $pages['magefan_blog_author_view'] = __('Blog Author Pages');
        $pages['magefan_blog_tag_view'] = __('Blog Tag Pages');

        return $pages;
    }

}