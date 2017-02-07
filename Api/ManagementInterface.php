<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Api;

interface ManagementInterface
{
    /**
     * Create new item.
     *
     * @api
     * @param string $data.
     * @return string.
     */
    public function create($data);

    /**
     * Update item by id.
     *
     * @api
     * @param int $id.
     * @param string $data.
     * @return string.
     */
    public function update($id, $data);

    /**
     * Remove item by id.
     *
     * @api
     * @param int $id.
     * @return bool.
     */
    public function delete($id);

    /**
     * Get item by id.
     *
     * @api
     * @param int $id.
     * @return bool.
     */
    public function get($id);

    /**
     * Get item by id and store id, only if item published
     *
     * @api
     * @param int $id
     * @param  int $storeId
     * @return bool.
     */
    public function view($id, $storeId);
}