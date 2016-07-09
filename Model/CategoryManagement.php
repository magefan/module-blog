<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

/**
 * Category management model
 */
class CategoryManagement extends AbstractManagement
{
    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magefan\Blog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_itemFactory = $categoryFactory;
    }

}
