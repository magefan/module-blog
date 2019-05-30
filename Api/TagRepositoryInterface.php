<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Tag;

/**
 * Interface TagRepositoryInterface
 * @package Magefan\Blog\Api
 */
interface TagRepositoryInterface
{
    /**
     * @param Tag $tag
     * @return mixed
     */
    public function save(Tag $tag);

    /**
     * @param $tagId
     * @return mixed
     */
    public function getById($tagId);

    /**
     * Retrieve Tag matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface
    $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param Tag $tag
     * @return mixed
     */
    public function delete(Tag $tag);

    /**
     * Delete Tag by ID.
     *
     * @param int $tagId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($tagId);
}
