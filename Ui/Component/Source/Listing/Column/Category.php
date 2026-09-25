<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Source\Listing\Column;

use Magefan\Blog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Post category column
 */
class Category extends Column
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryRepositoryInterface $categoryRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $categoryIds = $item['categories'] ?? [];
            if (!is_array($categoryIds) || empty($categoryIds)) {
                $item[$fieldName] = '';
                continue;
            }
            $names = [];
            foreach ($categoryIds as $id) {
                $names[] = $this->getCategoryTitle((int)$id);
            }
            $item[$fieldName] = implode(', ', array_filter($names));
        }

        return $dataSource;
    }

    /**
     * @param int $id
     * @return string
     */
    private function getCategoryTitle(int $id): string
    {
        if (!isset($this->cache[$id])) {
            try {
                $this->cache[$id] = $this->categoryRepository->getById($id)->getTitle();
            } catch (NoSuchEntityException $e) {
                $this->cache[$id] = '';
            }
        }
        return (string)$this->cache[$id];
    }
}