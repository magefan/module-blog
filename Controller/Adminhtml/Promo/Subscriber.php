<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Promo;

class Subscriber extends AbstractPromo
{
    /**
     * Get promo page title
     *
     * @return string
     */
    protected function getPageTitle(): string
    {
        return 'Comment Subscribers';
    }
}
