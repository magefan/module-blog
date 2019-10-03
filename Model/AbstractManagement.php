<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Api\ManagementInterface;

/**
 * Abstract management model
 */
abstract class AbstractManagement implements ManagementInterface
{
    /**
     * @var Magento\Framework\Model\AbstractModel
     */
    protected $_itemFactory;

    /**
     * Create new item using data
     *
     * @param string $data
     * @return string || false
     */
    public function create($data)
    {
        try {
            $data = json_decode($data, true);
            $item = $this->_itemFactory->create();
            $item->setData($data)->save();
            return json_encode($item->getData());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update item using data
     *
     * @param int $id
     * @param string $data
     * @return string || false
     */
    public function update($id, $data)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->load($id);

            if (!$item->getId()) {
                return false;
            }
            $data = json_decode($data, true);
            $item->addData($data)->save();
            return json_encode($item->getData());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete item by id
     *
     * @param  int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->load($id);
            if ($item->getId()) {
                $item->delete();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get item by id
     *
     * @param  int $id
     * @return bool
     */
    public function get($id)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->load($id);

            if (!$item->getId()) {
                return false;
            }
            return json_encode($item->getData());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get item by id and store id, only if item published
     *
     * @param  int $id
     * @param  int $storeId
     * @return bool
     */
    public function view($id, $storeId)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->getResource()->load($item, $id);

            if (!$item->isVisibleOnStore($storeId)) {
                return false;
            }

            return json_encode($item->getDynamicData());
        } catch (\Exception $e) {
            return false;
        }
    }
}
