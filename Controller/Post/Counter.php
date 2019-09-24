<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Post;

/**
 * Class Counter
 */
class Counter extends \Magefan\Blog\Controller\Post\View
{

    /**
     * @return $this|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $post = parent::_initPost();
        $viewCounts = (int) $post->getData('views_count');

        $viewCounts += 1;
        $post->setData('views_count',$viewCounts);
        $post->getResource()->save($post);

        return $this;
    }
}
