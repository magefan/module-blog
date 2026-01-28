<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Post;
use Magefan\Blog\Model\PostFactory;

/**
 * Interface PostRepositoryInterface
 */
interface PostRepositoryInterface
{
    /**
     * Retrieve Post factory instance.
     *
     * @return PostFactory
     */
    public function getFactory();

    /**
     * Save the provided post.
     *
     * @param Post $post
     * @return mixed
     */
    public function save(Post $post);

    /**
     * Retrieve Post by ID.
     *
     * @param int $postId
     * @return mixed
     */
    public function getById($postId);

    /**
     * Retrieve Post matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete the provided post.
     *
     * @param Post $post
     * @return mixed
     */
    public function delete(Post $post);

    /**
     * Delete Post by ID.
     *
     * @param int $postId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($postId);
}
