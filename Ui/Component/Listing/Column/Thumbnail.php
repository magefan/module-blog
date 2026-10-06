<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Listing\Column;

use Magefan\Blog\Model\Url as BlogUrl;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Post featured image thumbnail column
 */
class Thumbnail extends Column
{
    private const DEFAULT_EDIT_URL = 'blog/post/edit';
    private const DEFAULT_ID_FIELD = 'post_id';

    /**
     * @var BlogUrl
     */
    private $blogUrl;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param BlogUrl $blogUrl
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BlogUrl $blogUrl,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->blogUrl = $blogUrl;
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

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item[$fieldName])) {
                $src = $this->blogUrl->getMediaUrl($item[$fieldName]);
                $item[$fieldName . '_src'] = $src;
                $item[$fieldName . '_orig_src'] = $src;
                $item[$fieldName . '_alt'] = $item['title'] ?? '';
                $editUrl = $this->getData('config/editUrl') ?? self::DEFAULT_EDIT_URL;
                $idField = $this->getData('config/idField') ?? self::DEFAULT_ID_FIELD;
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    $editUrl,
                    ['id' => $item[$idField] ?? null]
                );
            }
        }

        return $dataSource;
    }
}
