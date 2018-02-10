<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Admin blog comment edit controller
 */
class Comment extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey  = 'blog_comment_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magefan_Blog::comment';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magefan\Blog\Model\Comment';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magefan_Blog::comment';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}
