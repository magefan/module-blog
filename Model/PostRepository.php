<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Api\PostRepositoryInterface;
use Magefan\Blog\Model\PostFactory;
use Magefan\Blog\Model\ResourceModel\Post as PostResourceModel;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class PostRepository
 * @package Magefan\Blog\Model
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var PostFactory
     */
    private $postFactory;
    /**
     * @var PostResourceModel
     */
    private $postResourceModel;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * PostRepository constructor.
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param PostResourceModel $postResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        PostFactory $postFactory,
        PostResourceModel $postResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->postFactory = $postFactory;
        $this->postResourceModel = $postResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param Post $post
     * @return bool|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function save(Post $post)
    {
        if ($post) {
            try {
                $this->postResourceModel->save($post);
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
            return $this->getById($post->getId());
        }
        return false;
    }

    /**
     * @param $postId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($postId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $post = $this->postFactory->create();
        $this->postResourceModel->load($post, $postId);
        if (!$post->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $post;
    }

    /**
     * @param Post $post
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(Post $post)
    {
        try {
            $this->postResourceModel->delete($post);
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
     * @param $postId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($postId)
    {
        return $this->delete($this->getById($postId));
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->collectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        /** @var \Magento\Framework\Api\searchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getData());

        return $searchResult;
    }
}
