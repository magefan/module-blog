<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\Component\Listing\Column;

/**
 * Class CategoryActions
 */
class CategoryActions extends AbstractActions
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'blog/category/edit';
    const URL_PATH_DELETE = 'blog/category/delete';
    const URL_PATH_DETAILS = 'blog/category/details';

    protected $indexId = 'category_id';
}
