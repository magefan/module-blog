<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\AbstractBlock;
use Magefan\Blog\Model\Url;

class Link extends AbstractBlock implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var Url
     */
    private $blogUrl;

    /**
     * @var null
     */
    private $modelRepository;

    /**
     * @var mixed
     */
    private $model;

    /**
     * Link constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param Url $blogUrl
     * @param mixed $modelRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Url $blogUrl,
        $modelRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->blogUrl = $blogUrl;
        $this->modelRepository = $modelRepository;
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $model = $this->getModel();
        if (!$model || !$model->getId()) {
            return '';
        }

        $href = $this->blogUrl->getUrl($model, $this->model->getControllerName());

        // Note: 'anchor_text' maps to $title and 'title' maps to $anchorText — yes, swapped.
        // Early naming mistake; kept intentionally to avoid breaking existing widget configs in DB.
        $title = $this->getData('anchor_text') ? trim($this->getData('anchor_text')) : '';
        if (!$title) {
            $title = $model->getTitle();
        }

        $anchorText = $this->getData('title') ? trim($this->getData('title')) : '';
        if (!$anchorText) {
            $anchorText = $model->getTitle();
        }

        if (!$href) {
            return $this->escapeHtml(__($anchorText));
        } else {
            return '<a href="' . $this->escapeUrl($href) . '" title="'
                . $this->escapeHtml(__($title)) . '">'
                . $this->escapeHtml(__($anchorText)) . '</a>';
        }
    }

    /**
     * Get model
     *
     * @return mixed
     */
    private function getModel()
    {
        if (null === $this->model) {
            $this->model = false;

            try {
                $id = $this->getData('entity_id') ? trim($this->getData('entity_id')) : '';
                if ($id) {
                    $this->model = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get($this->modelRepository['instance'])
                        ->getbyId($id);
                }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

            }
        }
        return $this->model;
    }
}
