<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Category;

/**
 * Blog category mass delete controller
 */
class MassDelete extends \Magefan\Blog\Controller\Adminhtml\Category
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::category_delete';

}