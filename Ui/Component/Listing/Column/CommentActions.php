<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\Component\Listing\Column;

/**
 * Class CommentActions
 */
class CommentActions extends AbstractActions
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'blog/comment/edit';
    const URL_PATH_DELETE = 'blog/comment/delete';
    const URL_PATH_DETAILS = 'blog/comment/details';

    protected $indexId = 'comment_id';
}
