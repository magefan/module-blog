<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Api\CategoryRepositoryInterface;
use Magefan\Blog\Model\CategoryFactory;
use Magefan\Blog\Model\ResourceModel\Category as CategoryResourceModel;
use Magefan\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Class CategoryRepository model
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var array
     */
    private $instances;

    /**
     * CategoryRepository constructor.
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     * @param CategoryResourceModel $categoryResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResourceModel $categoryResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
    }

    /**
     * @return CategoryFactory
     */
    public function getFactory()
    {
        return $this->categoryFactory;
    }

    /**
     * @param Category $category
     * @return bool|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(Category $category)
    {
        if ($category) {
            try {
                $this->categoryResourceModel->save($category);
            } catch (ConnectionException $exception) {
                throw new CouldNotSaveException(
                    __('Database connection error'),
                    $exception,
                    $exception->getCode()
                );
            } catch (CouldNotSaveException $e) {
                throw new CouldNotSaveException(__('Unable to save item'), $e);
            } catch (ValidatorException $e) {
                throw new CouldNotSaveException(__($e->getMessage()));
            }
            return $this->getById($category->getId());
        }
        return false;
    }

    /**
     * @param $categoryId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($categoryId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = implode('_', func_get_args());
        if (!isset($this->instances[$cacheKey])) {
            $category = $this->categoryFactory->create();
            $this->categoryResourceModel->load($category, $categoryId);
            if (!$category->getId()) {
                throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
            }
            $this->instances[$cacheKey] = $category;
        }
        return $this->instances[$cacheKey];
    }

    /**
     * @param Category $category
     * @return bool
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(Category $category)
    {
        try {
            $this->categoryResourceModel->delete($category);
        } catch (ValidatorException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove item')
            );
        }
        return true;
    }

    /**
     * @param $categoryId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($categoryId)
    {
        return $this->delete($this->getById($categoryId));
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Framework\Api\searchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getData());

        return $searchResult;
    }
}
