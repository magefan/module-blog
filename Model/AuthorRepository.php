<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Api\AuthorRepositoryInterface;
use Magefan\Blog\Api\AuthorInterface;
use Magefan\Blog\Api\AuthorInterfaceFactory;
use Magefan\Blog\Api\AuthorResourceModelInterface as AuthorResourceModel;
use Magefan\Blog\Api\AuthorCollectionInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class AuthorRepository
 * @package Magefan\Blog\Model
 */
class AuthorRepository implements AuthorRepositoryInterface
{
    /**
     * @var AuthorInterfaceFactory
     */
    private $authorFactory;
    /**
     * @var AuthorResourceModel
     */
    private $authorResourceModel;
    /**
     * @var AuthorCollectionInterfaceFactory
     */
    private $collectionFactory;
    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * AuthorRepository constructor.
     * @param AuthorInterface $authorFactory
     * @param AuthorResourceModel $authorResourceModel
     * @param AuthorCollectionInterfaceFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        AuthorInterfaceFactory $authorFactory,
        AuthorResourceModel $authorResourceModel,
        AuthorCollectionInterfaceFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->authorFactory = $authorFactory;
        $this->authorResourceModel = $authorResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param AuthorInterface $author
     * @return bool|Author|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(AuthorInterface $author)
    {
        if ($author) {
            try {
                $this->authorResourceModel->save($author);
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
            return $this->getById($author->getId());
        }
        return false;
    }

    /**
     * @param $authorId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return Author|mixed
     * @throws NoSuchEntityException
     */
    public function getById($authorId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $author = $this->authorFactory->create();
        $this->authorResourceModel->load($author, $authorId);
        if (!$author->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $author;
    }

    /**
     * @param AuthorInterface $author
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(AuthorInterface $author)
    {
        try {
            $this->authorResourceModel->delete($author);
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
     * @param int $authorId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($authorId)
    {
        return $this->delete($this->getById($authorId));
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Author\Collection $collection */
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
