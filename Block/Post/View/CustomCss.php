<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Post\View;

use Magefan\Blog\Block\Post\AbstractPost;

class CustomCss extends AbstractPost
{
    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $post = $this->getPost();
        if ($post && $post->getCustomCss()) {
            return '<style>' . strip_tags($post->getCustomCss()) . '</style>';
        }

        return '';
    }
}
