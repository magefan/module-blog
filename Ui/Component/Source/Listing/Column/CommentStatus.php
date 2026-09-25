<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Source\Listing\Column;

use Magefan\Blog\Model\Config\Source\CommentStatus as CommentStatusSource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Comment status column
 */
class CommentStatus extends Column
{
    /**
     * @var array
     */
    private $statuses = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CommentStatusSource $commentStatus
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CommentStatusSource $commentStatus,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->statuses = $commentStatus->toArray();
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
            $value = $item[$fieldName] ?? null;
            $label = $this->statuses[$value] ?? $value;
            if ((int)$value === CommentStatusSource::APPROVED) {
                $severity = 'notice';
            } elseif ((int)$value === CommentStatusSource::PENDING) {
                $severity = 'critical';
            } else {
                $severity = 'minor';
            }
            $item[$fieldName] = '<span class="grid-severity-' . $severity . '"><span>' . $label . '</span></span>';
        }

        return $dataSource;
    }
}