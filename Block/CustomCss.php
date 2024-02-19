<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block;

use Magefan\Blog\Block\Post\View\Opengraph;
class CustomCss extends Opengraph
{

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $css = $this->config->getCustomCss();
        return $css ? ('<style>' . $css . '</style>') : '';
    }
}
