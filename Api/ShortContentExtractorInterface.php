<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Api;

interface ShortContentExtractorInterface
{
    /**
     * Retrieve short filtered content
     * @param string$content
     * @param mixed $len
     * @param mixed $endCharacters
     * @return string
     * @throws \Exception
     */
    public function execute($content, $len = null, $endCharacters = null);

}
