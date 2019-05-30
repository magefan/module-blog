<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Post;

/**
 * Interface PostRepositoryInterface
 * @package Magefan\Blog\Api
 */
interface PostRepositoryInterface
{
    /**
     * @param Post $post
     * @return mixed
     */
    public function save(Post $post);

    /**
     * @param $postId
     * @return mixed
     */
    public function getById($postId);

    /**
     * Retrieve Post matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface
    $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
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