<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

class PostListWidgetTemplate extends Template
{
    /**
     * @inheritdoc
     */
    public function toOptionArray():array
    {
        $result = parent::toOptionArray();
        $result['custom_template'] = 'Custom Template';
        return $result;
    }
}
