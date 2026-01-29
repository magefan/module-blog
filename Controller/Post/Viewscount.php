<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Controller\Post;

/**
 * Class Count increment views_count value
 */
class Viewscount extends View
{

    /**
     * Execute action
     *
     * @return $this|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(): void
    {
        $post = parent::_initPost();
        if ($post && $post->getId()) {
            $post->getResource()->incrementViewsCount($post);
        }
    }
}
