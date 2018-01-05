<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\Component\Listing\Column;


/**
 * Class PostActions
 */
class PostActions extends AbstractActions
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'blog/post/edit';
    const URL_PATH_DELETE = 'blog/post/delete';
    const URL_PATH_DETAILS = 'blog/post/details';

    protected $indexId = 'post_id';
}
