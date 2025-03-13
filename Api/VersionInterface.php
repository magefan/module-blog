<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

interface VersionInterface
{
    /**
     * get blog version and edition
     *
     * @api
     * @return string
     */
    public function getVersion(): string;
}
