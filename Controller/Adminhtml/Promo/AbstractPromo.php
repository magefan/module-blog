<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Promo;

use Magefan\Community\Controller\Adminhtml\AbstractPromo as CommunityAbstractPromo;

abstract class AbstractPromo extends CommunityAbstractPromo
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magefan_Blog::elements';

    /**
     * @inheritdoc
     */
    protected function getActiveMenu(): string
    {
        return 'Magefan_Blog::elements';
    }
}
