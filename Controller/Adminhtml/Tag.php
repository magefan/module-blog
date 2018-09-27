<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Admin blog tag edit controller
 */
class Tag extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey  = 'blog_tag_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magefan_Blog::tag';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magefan\Blog\Model\Tag';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magefan_Blog::tag';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}
