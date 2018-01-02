<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\DataProvider\Category\Related;

use \Magento\Ui\DataProvider\AbstractDataProvider;
use Magefan\Blog\Model\ResourceModel\Category\Collection;
use Magefan\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class CategoryDataProvider
 */
class CategoryDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var category
     */
    private $category;

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
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
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

        if (!$this->getCategory()) {
            return $collection;
        }

        $collection->addFieldToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getCategory()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * Retrieve category
     *
     * @return CategoryInterface|null
     */
    protected function getCategory()
    {
        if (null !== $this->category) {
            return $this->category;
        }

        if (!($id = $this->request->getParam('current_category_id'))) {
            return null;
        }

        return $this->category = $this->categoryRepository->getById($id);
    }
}
