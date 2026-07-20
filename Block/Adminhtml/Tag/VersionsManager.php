<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Tag;

class VersionsManager extends \Magefan\Blog\Block\Adminhtml\VersionsManager
{
    /**
     * @return array
     */
    public function getExtraSelectors(): array
    {
        return [
            '[data-index="websites"]',
            '[name="posts_list_template"]',
        ];
    }

    /**
     * @return array
     */
    public function getPlusSelectors(): array
    {
        return [
            '[name="url_key_create_redirect"]',
            '[upload-area-id="tag_img"]',
            '[name="tag_img_alt"]',
        ];
    }
}
