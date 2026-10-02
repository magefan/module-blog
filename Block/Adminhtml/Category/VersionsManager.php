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
            '[name="posts_list_template"]',
            '[data-index="posts"]',
            '.store-switcher'
        ];
    }

    /**
     * @return array
     */
    public function getPlusSelectors(): array
    {
        return [
            '[name="url_key_create_redirect"]',
            '.category_img .file-uploader.image-uploader',
            '[name="category_img_alt"]',
            '[data-index="bottom_content"]',
            '[name="groups"]'
        ];
    }
}
