<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Used in creating options for commetns config value selection
 */
class DesignVersion implements \Magento\Framework\Option\ArrayInterface
{
	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return [
			['value' => '2024-12', 'label' => '2024-12'],
			['value' => '2025-04', 'label' => '2025-04']
		];
	}

	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray()
	{
		$array = [];
		foreach ($this->toOptionArray() as $item) {
			$array[$item['value']] = $item['label'];
		}
		return $array;
	}
}
