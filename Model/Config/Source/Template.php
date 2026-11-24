<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

use Magefan\Blog\Model\TemplatePool;

class Template implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var TemplatePool
     */
    private $templatePool;

    /**
     * @var string
     */
    private $templateType;

    /**
     * @var array
     */
    private $options;

    /**
     * Template constructor.
     * @param TemplatePool $templatePool
     * @param string $templateType
     */
    public function __construct(
        TemplatePool $templatePool,
        string $templateType
    ) {
        $this->templatePool = $templatePool;
        $this->templateType = $templateType;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray():array
    {
        if (!$this->templateType) {
            return [];
        }

        if (!isset($this->options[$this->templateType])) {
            if ($this->templateType === 'blog_post_list') {
                $this->options[$this->templateType] = [
                    ['label' => __('List (Blog Extra)'), 'value' => []],
                    ['label' => __('Grid - 1 column (Blog Extra)'), 'value' => []],
                    ['label' => __('Grid - 2 columns (Blog Extra)'), 'value' => []],
                    ['label' => __('Grid - 3 columns (Blog Extra)'), 'value' => []],
                    ['label' => __('Grid and List (Blog Extra)'), 'value' => []],
                    ['label' => __('Slider (Blog Extra)'), 'value' => []],
                    ['label' => __('Other Grids (Blog Extra)'), 'value' => []],
                ];

                $groupMap = [
                    'list' => 0,
                    'grid1' => 1,
                    'grid2' => 2,
                    'grid3' => 3,
                    'grid_and_list' => 4,
                    'slider' => 5,
                    'other' => 6
                ];
                $ungroupedOptions = [];
                foreach ($this->templatePool->getAll($this->templateType) as $value => $info) {
                    $option = [
                        'value' => $info['value'],
                        'label' => $info['label']
                    ];
                    if (isset($info['group']) && isset($groupMap[$info['group']])) {
                        $index = $groupMap[$info['group']];
                        $this->options[$this->templateType][$index]['value'][] = $option;
                    } else {
                        $ungroupedOptions[] = ['value' => $info['value'], 'label' => $info['label']];
                    }
                }
                $this->options[$this->templateType] = array_filter(
                    $this->options[$this->templateType],
                    function ($group): bool {
                        return !empty($group['value']);
                    }
                );
                $this->options[$this->templateType] = array_merge(
                    $ungroupedOptions,
                    $this->options[$this->templateType]
                );
            } else {
                $this->options[$this->templateType] = [];
                foreach ($this->templatePool->getAll($this->templateType) as $value => $info) {
                    $this->options[$this->templateType][] = ['value' => $info['value'], 'label' => $info['label']];
                }
            }
        }
        return $this->options[$this->templateType];
    }
}
