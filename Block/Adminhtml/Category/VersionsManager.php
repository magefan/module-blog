<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Category;

class VersionsManager extends \Magefan\Blog\Block\Adminhtml\VersionsManager
{
    /**
     * @return array
     */
    public function getExtraSelectors(): array
    {
        return [
            '[name="posts_sort_by"]',
            '[name="posts_list_template"]',
            '.admin__fieldset-section.category [data-index="button_product"]',
            '[data-index="websites"]',
        ];
    }

    /**
     * @return array
     */
    public function getPlusSelectors(): array
    {
        return [
            '[name="url_key_create_redirect"]',
            '[upload-area-id="category_img"]',
            '[name="category_img_alt"]',
            '[data-index="bottom_content"]',
            '[name="groups"]'
        ];
    }
}
