<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Model\Config\Source;

/**
 * Used in edit post form
 *
 */
class CategoryTree implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var array
     */
    protected $_childs;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $authorCollectionFactory
     * @param void
     */
    public function __construct(
        \Magefan\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->_getOptions();
        }
        return $this->_options;
    }

    /**
     * @return array<mixed, array<'label'|'optgroup'|'value', mixed>>
     */
    protected function _getOptions($itemId = 0): array
    {
        $childs =  $this->_getChilds();
        $options = [];

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $item) {
                $data = [
                    'label' => $item->getTitle() .
                        (' (ID: ' . $item->getId() . ')') .
                        ($item->getIsActive() ? '' : ' ('.__('Disabled').')'),
                    'value' => $item->getId(),
                ];
                if (isset($childs[$item->getId()])) {
                    $data['optgroup'] = $this->_getOptions($item->getId());
                }

                $options[] = $data;
            }
        }

        return $options;
    }

    protected function _getChilds()
    {
        if ($this->_childs === null) {
            $this->_childs =  $this->_categoryCollectionFactory->create()
                ->setOrder('position')
                ->getGroupedChilds();
        }
        return $this->_childs;
    }
}
