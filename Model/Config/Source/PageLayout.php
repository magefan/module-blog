<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PageLayout implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    private $pageLayoutBuilder;

    /**
     * @var
     */
    protected $_options;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     */
    public function __construct(\Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('-- Please Select --')]);
        $this->_options = $options;

        return $options;
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
