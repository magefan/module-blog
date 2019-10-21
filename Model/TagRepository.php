<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Api\TagRepositoryInterface;
use Magefan\Blog\Model\TagFactory;
use Magefan\Blog\Model\ResourceModel\Tag as TagResourceModel;
use Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory;
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
 * Class TagRepository model
 */
class TagRepository implements TagRepositoryInterface
{
    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var TagResourceModel
     */
    private $tagResourceModel;

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
     * TagRepository constructor.
     * @param TagFactory $tagFactory
     * @param TagResourceModel $tagResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
    }

    /**
     * @return TagFactory
     */
    public function getFactory()
    {
        return $this->tagFactory;
    }

    /**
     * @param Tag $tag
     * @return bool|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(Tag $tag)
    {
        if ($tag) {
            try {
                $this->tagResourceModel->save($tag);
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
            return $this->getById($tag->getId());
        }
        return false;
    }

    /**
     * @param $tagId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return Tag|mixed
     * @throws NoSuchEntityException
     */
    public function getById($tagId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $tag = $this->tagFactory->create();
        $this->tagResourceModel->load($tag, $tagId);
        if (!$tag->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $tag;
    }

    /**
     * @param Tag $tag
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(Tag $tag)
    {
        try {
            $this->tagResourceModel->delete($tag);
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
     * @param int $tagId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($tagId)
    {
        return $this->delete($this->getById($tagId));
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Tag\Collection $collection */
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
