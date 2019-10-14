<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Admin blog post edit controller
 */
class Post extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey  = 'blog_post_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magefan_Blog::post';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = \Magefan\Blog\Model\Post::class;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magefan_Blog::post';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}
