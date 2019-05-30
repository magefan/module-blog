<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Author;

/**
 * Interface AuthorRepositoryInterface
 * @package Magefan\Blog\Api
 */
interface AuthorRepositoryInterface
{
    /**
     * @param Author $author
     * @return mixed
     */
    public function save(Author $author);

    /**
     * @param $authorId
     * @return mixed
     */
    public function getById($authorId);

    /**
     * Retrieve Author matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface
    $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param Author $author
     * @return mixed
     */
    public function delete(Author $author);

    /**
     * Delete Author by ID.
     *
     * @param int $authorId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($authorId);
}
