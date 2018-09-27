<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Used in recent post widget
 *
 */
class Category implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $authorCollectionFactory
     * @param void
     */
    public function __construct(
        \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [['label' => __('Please select'), 'value' => 0]];
            $collection = $this->categoryCollectionFactory->create();
            $collection->setOrder('position')
                ->getTreeOrderedArray();

            foreach ($collection as $item) {
                $this->options[] = [
                    'label' => $this->_getSpaces($item->getLevel()) . ' ' . $item->getTitle() .
                        ($item->getIsActive() ? '' : ' ('.__('Disabled').')'),
                    'value' => $item->getId(),
                ];
            }
        }

        return $this->options;
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

    /**
     * Generate spaces
     * @param  int $n
     * @return string
     */
    protected function _getSpaces($n)
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }

        return $s;
    }
}
