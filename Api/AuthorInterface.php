<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

interface AuthorInterface
{
    /**
     * Determines if the item is visible on the specified store.
     *
     * @param int $storeId
     * @return bool
     */
    public function isVisibleOnStore(int $storeId): bool;
}
