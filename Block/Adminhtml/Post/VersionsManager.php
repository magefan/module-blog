<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Post;

class VersionsManager extends \Magefan\Blog\Block\Adminhtml\VersionsManager
{
    /**
     * @return array
     */
    public function getExtraSelectors(): array
    {
        return [
            '[name="end_time"]',
            '[name="post_video"]',
            '[data-index="container_coauthors"]',
        ];
    }

    /**
     * @return array
     */
    public function getPlusSelectors(): array
    {
        return [
            '[name="groups"]',
            '[name="featured_list_img_alt"]',
            '[name="enable_comments"]',
            '[name="url_key_create_redirect"]',
            '[upload-area-id="featured_list_img"]',
            '#blog_post_formrule_conditions_fieldset_',
            '[data-index="display_on_product"]',
            '[data-index="display_on_post"]',
            '[data-index="auto_related"]',
            '[data-index="related_by_rule"]',
        ];
    }
}
