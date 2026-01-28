<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

/**
 * Interface AuthorRepositoryInterface
 */
interface AuthorRepositoryInterface
{
    /**
     * Retrieve Author factory instance.
     *
     * @return AuthorInterfaceFactory
     */
    public function getFactory();

    /**
     * Save the provided author.
     *
     * @param AuthorInterface $author
     * @return mixed
     */
    public function save(AuthorInterface $author);

    /**
     * Retrieve Author by ID.
     *
     * @param int $authorId
     * @return mixed
     */
    public function getById($authorId);

    /**
     * Retrieve Author matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete the provided author.
     *
     * @param AuthorInterface $author
     * @return mixed
     */
    public function delete(AuthorInterface $author);

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
