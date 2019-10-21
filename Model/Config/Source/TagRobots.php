<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

use Magento\Config\Model\Config\Source\Design\Robots;

/**
 * Class Tag Robots Model
 */
class TagRobots extends Robots
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => '', 'label' => 'Use config settings']);
        return $options;
    }
}
