<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Comment;
use Magefan\Blog\Model\CommentFactory;

/**
 * Interface CommentRepositoryInterface
 */
interface CommentRepositoryInterface
{
    /**
     * Retrieve Comment factory instance.
     *
     * @return CommentFactory
     */
    public function getFactory();

    /**
     * Save the provided comment.
     *
     * @param Comment $comment
     * @return mixed
     */
    public function save(Comment $comment);

    /**
     * Retrieve Comment by ID.
     *
     * @param int $commentId
     * @return mixed
     */
    public function getById($commentId);

    /**
     * Retrieve Comment matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete the provided comment.
     *
     * @param Comment $comment
     * @return mixed
     */
    public function delete(Comment $comment);

    /**
     * Delete Comment by ID.
     *
     * @param int $commentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($commentId);
}
