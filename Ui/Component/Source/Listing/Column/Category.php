<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Ui\Component\Source\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Category
 */
class Category extends Column
{
    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * Category constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ResourceConnection $resource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_resource = $resource;
    }

    /**
     * Prepare Data Source for actions column on dynamic grid
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('magefan_blog_category');

        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['categories'])) {
                    if (count($item['categories']) > 1) {
                        $select = $connection->select()
                            ->from($tableName, 'title')
                            ->where('category_id IN(?)', $item['categories']);
                        $item[$this->getData('name')] = [
                            implode(', ', $connection->fetchCol($select))
                        ];
                    } else {
                        foreach ($item['categories'] as $categoryId) {
                            $select = $connection->select()
                                ->from($tableName, 'title')
                                ->where('category_id = ?', $categoryId);
                            $item[$this->getData('name')] = [
                                $connection->fetchOne($select)
                            ];
                        }
                    }
                }
            }
        }
        return $dataSource;
    }
}
