<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

/**
 * Interface UrlResolverInterface
 */
interface UrlResolverInterface
{
    /**
     * Resolve path to url
     *
     * @param string $path
     * @return array
     */
    public function resolve($path);
}
