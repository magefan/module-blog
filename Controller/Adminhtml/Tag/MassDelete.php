<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Tag;

/**
 * Blog tag mass delete controller
 */
class MassDelete extends \Magefan\Blog\Controller\Adminhtml\Tag
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::tag_delete';
}