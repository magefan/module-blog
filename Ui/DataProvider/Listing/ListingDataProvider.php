<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Ui\DataProvider\Listing;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Generic listing data provider for blog entities
 */
class ListingDataProvider extends AbstractDataProvider
{

    /**
     * Fields resolved for the current collection instance.
     *
     * @var array
     */
    private $fulltextFields;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param array $fulltextFields
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        Collection $collection,
        array $fulltextFields = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->fulltextFields = $fulltextFields;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() === 'fulltext') {
            $fields = $this->fulltextFields;
            if ($fields) {
                $value = '%' . str_replace(['%', '_'], ['\%', '\_'], (string)$filter->getValue()) . '%';
                $this->collection->addFieldToFilter(
                    $fields,
                    array_fill(0, count($fields), ['like' => $value])
                );
            }
            return;
        }

        parent::addFilter($filter);
    }
}