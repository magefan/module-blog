<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Tag;
use Magefan\Blog\Model\TagFactory;

/**
 * Interface TagRepositoryInterface
 */
interface TagRepositoryInterface
{
    /**
     * Retrieve Tag factory instance.
     *
     * @return TagFactory
     */
    public function getFactory();

    /**
     * Save the provided tag.
     *
     * @param Tag $tag
     * @return mixed
     */
    public function save(Tag $tag);

    /**
     * Retrieve Tag by ID.
     *
     * @param int $tagId
     * @return mixed
     */
    public function getById($tagId);

    /**
     * Retrieve Tag matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete the provided tag.
     *
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
