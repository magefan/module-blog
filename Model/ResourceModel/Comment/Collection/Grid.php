<?php
/**
 * Copyright © 2015-2017 Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Comment\Collection;

use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use \Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use \Magento\Framework\Event\ManagerInterface as EventManager;
use \Psr\Log\LoggerInterface as Logger;
use \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use \Magento\Store\Model\StoreManagerInterface;

class Grid extends SearchResult
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        StoreManagerInterface $storeManager,
        $mainTable = 'magefan_blog_comment',
        $resourceModel = 'Magefan\Blog\Model\ResourceModel\Comment',
        $identifierName = null,
        $connectionName = null

    ) {
        if (property_exists($this, 'identifierName')) {
            /* magento > 2.2.x */
            parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
        } else {
            /* magento = 2.1.x */
            parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        }

        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['post' => $this->getTable('magefan_blog_post')],
                'main_table.post_id = post.post_id',
                ['title' => 'title']
            );

        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('post_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $tableName = $this->getTable('magefan_blog_post_store');
            $select = $connection->select()
                ->from(['cps' => $tableName])
                ->where('cps.post_id IN (?)', $items);

            $result = [];
            foreach ($connection->fetchAll($select) as $item) {
                if (!isset($result[$item['post_id']])) {
                    $result[$item['post_id']] = [];
                }
                $result[$item['post_id']][] = $item['store_id'];
            }

            if ($result) {
                foreach ($this as $item) {
                    $postId = $item->getData('post_id');
                    if (!isset($result[$postId])) {
                        continue;
                    }
                    if ($result[$postId] == 0) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                    } else {
                        $storeId = $result[$item->getData('post_id')];
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_ids', $result[$postId]);
                }
            }

            foreach ($this as $item) {
                if ($this->_storeId) {
                    $item->setStoreId($this->_storeId);
                }
            }
        }

        $this->_previewFlag = false;
        return parent::_afterLoad();
    }
}
