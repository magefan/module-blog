<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Api;

use Magefan\Blog\Model\Category;
use Magefan\Blog\Model\CategoryFactory;

/**
 * Interface PostRepositoryInterface
 */
interface CategoryRepositoryInterface
{
    /**
     * Retrieve Category factory instance.
     *
     * @return CategoryFactory
     */
    public function getFactory();

    /**
     * Save the provided category.
     *
     * @param Category $category
     * @return mixed
     */
    public function save(Category $category);

    /**
     * Retrieve Category by ID.
     *
     * @param int $categoryId
     * @return mixed
     */
    public function getById($categoryId);

    /**
     * Retrieve Category matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete the provided category.
     *
     * @param Category $category
     * @return mixed
     */
    public function delete(Category $category);

    /**
     * Delete Category by ID.
     *
     * @param int $categoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($categoryId);
}
