<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Comment\Collection;

use Magefan\Blog\Model\ResourceModel\Comment\Collection;

class Grid extends Collection
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
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
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
