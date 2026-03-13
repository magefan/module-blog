<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

class InfoBlogPlus extends InfoPlan
{

    /**
     * Retrieves the name of the minimum plan required.
     *
     * @return string
     */
    protected function getMinPlan(): string
    {
        return 'Plus';
    }

    /**
     * Encodes a predefined list of section identifiers into a JSON string.
     *
     * @return string
     */
    protected function getSectionsJson(): string
    {
        $sections = json_encode([
            'mfblog_post_view_related_posts_autorelated_enabled',
            'mfblog_post_view_related_products_autorelated_enabled',
            'mfblog_post_view_comments_format_date',
            'mfblog_sidebar_archive_format_date',
            'mfblog_post_view_related_products_autorelated_black_words',
            'mfblog_post_view_related_posts_autorelated_black_words',
            'mfblog_design',
            'mfblog_advanced_permalink',
            'mfblog_sitemap',
            'mfblog_product_page_include_post_rich_snippet'
        ]);
        return $sections;
    }

    /**
     * Retrieves a translatable text describing the availability of an option based on a specific plan.
     *
     * @return string
     */
    protected function getText(): string
    {
        return (string)__("This option is available in <strong>Plus or Extra</strong> plans only.");
    }
}
