<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Grid\Column\Filter;

/**
 * Author grid filter
 */
class Author extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var \Magefan\Blog\Api\AuthorCollectionInterfaceFactory
     */
    protected $collectionFactory;

    /**
     * Author constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magefan\Blog\Api\AuthorCollectionInterfaceFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magefan\Blog\Api\AuthorCollectionInterfaceFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        $options[] = ['value' => '', 'label' => __('All Authors')];
        foreach ($this->collectionFactory->create()->load() as $item) {
            $options[] = ['value' => $item->getId(), 'label' => $item->getTitle()];
        };
        return $options;
    }
}
