<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Api\CommentRepositoryInterface;
use Magefan\Blog\Model\CommentFactory;
use Magefan\Blog\Model\ResourceModel\Comment as CommentResourceModel;
use Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class CommentRepository
 * @package Magefan\Blog\Model
 */
class CommentRepository implements CommentRepositoryInterface
{
    /**
     * @var CommentFactory
     */
    private $commentFactory;
    /**
     * @var CommentResourceModel
     */
    private $commentResourceModel;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * CommentRepository constructor.
     * @param \Magefan\Blog\Model\CommentFactory $commentFactory
     * @param CommentResourceModel $commentResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        CommentFactory $commentFactory,
        CommentResourceModel $commentResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->commentFactory = $commentFactory;
        $this->commentResourceModel = $commentResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param Comment $comment
     * @return bool|Comment|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(Comment $comment)
    {
        if ($comment) {
            try {
                $this->commentResourceModel->save($comment);
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
            return $this->getById($comment->getId());
        }
        return false;
    }

    /**
     * @param $commentId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return Comment|mixed
     * @throws NoSuchEntityException
     */
    public function getById($commentId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $comment = $this->commentFactory->create();
        $this->commentResourceModel->load($comment, $commentId);
        if (!$comment->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $comment;
    }

    /**
     * @param Comment $comment
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(Comment $comment)
    {
        try {
            $this->commentResourceModel->delete($comment);
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
     * @param int $commentId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($commentId)
    {
        return $this->delete($this->getById($commentId));
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Comment\Collection $collection */
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
