<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View\Comments;

use Magefan\Blog\Model\Config\Source\CommetType;

/**
 * Blog post Google comments block
 */
class Google extends \Magefan\Blog\Block\Post\View\Comments
{
    protected $commetType = CommetType::GOOGLE;
}
