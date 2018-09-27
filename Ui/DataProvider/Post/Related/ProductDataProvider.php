<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\DataProvider\Post\Related;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider as DataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class ProductDataProvider
 */
class ProductDataProvider extends DataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var post
     */
    private $post;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->productLinkRepository = $productLinkRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');

        return $collection;
    }

    /**
     * Add specific filters
     *
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
     * @return PostInterface|null
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
