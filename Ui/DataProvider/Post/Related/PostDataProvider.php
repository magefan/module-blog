<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\DataProvider\Post\Related;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magefan\Blog\Model\ResourceModel\Post\Collection;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class PostDataProvider get post data
 */
class PostDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var post
     */
    private $post;

    /**
     * @var \Magefan\Blog\Api\PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     * @param \Magefan\Blog\Api\PostRepositoryInterface $postRepository
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        \Magefan\Blog\Api\PostRepositoryInterface $postRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->postRepository = $postRepository;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();

        if (!$this->getPost()) {
            return $collection;
        }

        $collection->addFieldToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getPost()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    protected function addCollectionFilters(Collection $collection)
    {
        return $collection;
    }

    /**
     * Retrieve post
     *
     * @return \Magefan\Blog\Model\Post|null
     */
    protected function getPost()
    {
        if (null !== $this->post) {
            return $this->post;
        }

        if (!($id = $this->request->getParam('current_post_id'))) {
            return null;
        }

        return $this->post = $this->postRepository->getById($id);
    }
}
