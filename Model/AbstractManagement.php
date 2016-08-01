<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
        } catch (\Exception $e) {
            return false;
        }

        return json_encode($item->getData());
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
        } catch (\Exception $e) {
            return false;
        }

        return json_encode($item->getData());
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

}
