<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Source\Listing\Column;

use Magefan\Blog\Api\AuthorRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Post author column
 */
class Author extends Column
{
    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AuthorRepositoryInterface $authorRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AuthorRepositoryInterface $authorRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->authorRepository = $authorRepository;
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
            $authorId = $item['author_id'] ?? null;
            $item[$fieldName] = $authorId ? $this->getAuthorName((int)$authorId) : '';
        }

        return $dataSource;
    }

    /**
     * @param int $id
     * @return string
     */
    private function getAuthorName(int $id): string
    {
        if (!isset($this->cache[$id])) {
            try {
                $this->cache[$id] = $this->authorRepository->getById($id)->getName();
            } catch (NoSuchEntityException $e) {
                $this->cache[$id] = '';
            }
        }
        return (string)$this->cache[$id];
    }
}