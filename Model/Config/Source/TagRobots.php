<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

use Magento\Config\Model\Config\Source\Design\Robots;

class TagRobots extends Robots
{
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options,  ['value' => '', 'label' => 'Use config settings']);
        return $options;
    }
}