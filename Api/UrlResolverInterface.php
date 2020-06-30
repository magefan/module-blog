<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Api;

/**
 * Interface UrlResolverInterface
 */
interface UrlResolverInterface
{
    /**
     * @param string $path
     * @return array
     */
    public function resolve($path);
}
