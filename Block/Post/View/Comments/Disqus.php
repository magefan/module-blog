<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View\Comments;

use Magefan\Blog\Model\Config\Source\CommetType;

/**
 * Blog post Disqus comments block
 */
class Disqus extends \Magefan\Blog\Block\Post\View\Comments
{
    protected $commetType = CommetType::DISQUS;
}
