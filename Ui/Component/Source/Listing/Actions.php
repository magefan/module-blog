<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Source\Listing;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Generic listing actions column — configure via XML data/config:
 *   editUrlPath, deleteUrlPath, nameField, deleteConfirmMessage
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $idField = $this->getData('config/indexField');
        $editUrl = $this->getData('config/editUrlPath');
        $deleteUrl = $this->getData('config/deleteUrlPath');
        $nameField = $this->getData('config/nameField') ?: 'title';
        $deleteMessage = $this->getData('config/deleteConfirmMessage') ?: 'Are you sure you want to delete this item?';

        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item[$idField])) {
                continue;
            }
            $id = $item[$idField];
            $item[$name]['edit'] = [
                'href'  => $this->urlBuilder->getUrl($editUrl, ['id' => $id]),
                'label' => __('Edit'),
            ];
            $item[$name]['delete'] = [
                'href'    => $this->urlBuilder->getUrl($deleteUrl, ['id' => $id]),
                'label'   => __('Delete'),
                'confirm' => [
                    'title'   => __('Delete "%1"', $item[$nameField] ?? ''),
                    'message' => __($deleteMessage),
                ],
                'post' => true,
            ];
        }

        return $dataSource;
    }
}