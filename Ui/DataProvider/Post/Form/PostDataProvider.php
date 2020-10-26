<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Ui\DataProvider\Post\Form;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 */
class PostDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magefan\Blog\Model\ResourceModel\Post\Collection
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
     * @param CollectionFactory $postCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $postCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $postCollectionFactory->create();
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
        /** @var $post \Magefan\Blog\Model\Post */
        foreach ($items as $post) {
            $post = $post->load($post->getId()); //temporary fix
            $data = $post->getData();

            /* Prepare Featured Image */
            $map = [
                'featured_img' => 'getFeaturedImage',
                'featured_list_img' => 'getFeaturedListImage',
                'og_img' => 'getOgImage'
            ];
            foreach ($map as $key => $method) {
                if (isset($data[$key])) {
                    $name = $data[$key];
                    unset($data[$key]);
                    $data[$key][0] = [
                        'name' => $name,
                        'url' => $post->$method(),
                    ];
                }
            }

            $data['data'] = ['links' => []];

            /* Prepare related posts */
            $collection = $post->getRelatedPosts();
            $items = [];
            foreach ($collection as $item) {
                    $itemData = $item->getData();
                    $itemData['id'] = $item->getId();
                    /* Fix for big request data array */
                foreach (['content', 'short_content', 'meta_description'] as $field) {
                    if (isset($itemData[$field])) {
                        unset($itemData[$field]);
                    }
                }
                    /* End */
                    $items[] = $itemData;
            }
            $data['data']['links']['post'] = $items;

            /* Prepare related products */
            $collection = $post->getRelatedProducts()->addAttributeToSelect('name');
            $items = [];
            foreach ($collection as $item) {
                $itemData = $item->getData();
                $itemData['id'] = $item->getId();
                /* Fix for big request data array */
                foreach ($itemData as $key => $value) {
                    if (!in_array($key, ['entity_id', 'position', 'display_on_product', 'display_on_post', 'auto_related', 'related_by_rule', 'name', 'store_id', 'id'])) {
                        unset($itemData[$key]);
                    }
                }
                /* End */

                $items[] = $itemData;
            }
            $data['data']['links']['product'] = $items;

            /* Set data */
            $this->loadedData[$post->getId()] = $data;
        }

        $data = $this->dataPersistor->get('blog_post_form_data');
        if (!empty($data)) {
            $post = $this->collection->getNewEmptyItem();
            $post->setData($data);
            $this->loadedData[$post->getId()] = $post->getData();
            $this->dataPersistor->clear('blog_post_form_data');
        }

        return $this->loadedData;
    }
}
