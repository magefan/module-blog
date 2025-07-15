<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Admin blog category edit controller
 */
class Category extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey  = 'blog_category_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magefan_Blog::category';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = \Magefan\Blog\Model\Category::class;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magefan_Blog::category';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}
