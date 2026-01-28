<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

use Magento\Config\Model\Config\Source\Design\Robots;

/**
 * Class Tag Robots Model
 */
class TagRobots extends Robots
{
    /**
     * Returns an array of options with a default "Use config settings" option prepended.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => '', 'label' => 'Use config settings']);
        return $options;
    }
}
