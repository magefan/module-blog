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
     * Get selectors of Blog Extra features
     *
     * @return array
     */
    public function getExtraSelectors(): array
    {
        return [
            '[name="posts_list_template"]',
            '.store-switcher'
        ];
    }

    /**
     * Get selectors of Blog Plus features
     *
     * @return array
     */
    public function getPlusSelectors(): array
    {
        return [
            '[name="url_key_create_redirect"]',
            '.tag_img .file-uploader.image-uploader',
            '[name="tag_img_alt"]',
        ];
    }
}
