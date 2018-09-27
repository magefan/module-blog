<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Ui\DataProvider\Category\Form;

use Magefan\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 */
class CategoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magefan\Blog\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $categoryCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categoryCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $category \Magefan\Blog\Model\Category */
        foreach ($items as $category) {
            $category = $category->load($category->getId()); //temporary fix
            $this->loadedData[$category->getId()] = $category->getData();
        }

        $data = $this->dataPersistor->get('blog_category_form_data');
        if (!empty($data)) {
            $category = $this->collection->getNewEmptyItem();
            $category->setData($data);
            $this->loadedData[$category->getId()] = $category->getData();
            $this->dataPersistor->clear('blog_category_form_data');
        }

        return $this->loadedData;
    }
}
