<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

/**
 * Post management model
 */
class PostManagement extends AbstractManagement
{
    /**
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     */
    public function __construct(
        \Magefan\Blog\Model\PostFactory $postFactory
    ) {
        $this->_itemFactory = $postFactory;
    }

}
