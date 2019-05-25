<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Amp\Ldjson;

/**
 * Blog post list ldJson block
 */
class Post extends \Magefan\Blog\Block\Post\View\Richsnippets
{
    /**
     * Retrieve page structure structure data in JSON
     *
     * @return string
     */
    public function getJson()
    {
        $json = parent::getOptions();
        return str_replace('\/', '/', json_encode($json));
    }
}
