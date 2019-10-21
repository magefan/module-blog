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
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Class CommentRepository model
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
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * CommentRepository constructor.
     * @param \Magefan\Blog\Model\CommentFactory $commentFactory
     * @param CommentResourceModel $commentResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        CommentFactory $commentFactory,
        CommentResourceModel $commentResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->commentFactory = $commentFactory;
        $this->commentResourceModel = $commentResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
    }

    /**
     * @return CommentFactory
     */
    public function getFactory()
    {
        return $this->commentFactory;
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

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Framework\Api\searchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getData());

        return $searchResult;
    }
}
