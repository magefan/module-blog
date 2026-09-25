<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Source\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Post store view column
 */
class Store extends Column
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
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
            $item[$fieldName] = $this->prepareItem($item);
        }

        return $dataSource;
    }

    /**
     * @param array $item
     * @return string
     */
    private function prepareItem(array $item): string
    {
        $storeIds = $item['store_ids'] ?? [];
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        if (empty($storeIds)) {
            return '';
        }
        if (in_array(0, $storeIds)) {
            return (string)__('All Store Views');
        }

        $content = '';
        $data = $this->systemStore->getStoresStructure(false, $storeIds);
        foreach ($data as $website) {
            $content .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($group['label']) . '<br/>';
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . '<br/>';
                }
            }
        }

        return $content;
    }
}