<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form\Featured\Renderer;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magefan\Blog\Block\Adminhtml\System\Config\Form\Featured\Grid;
use Magento\Framework\View\LayoutFactory;

class GridElement extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        Factory             $factoryElement,
        CollectionFactory   $factoryCollection,
        Escaper             $escaper,
        LayoutFactory       $layoutFactory,
        array               $data = []
    ) {
        $this->layoutFactory = $layoutFactory;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getElementHtml(): string
    {
        $layout = $this->layoutFactory->create();

        if (!$layout->getBlock('posts.grid')) {
            $layout->createBlock(
                Grid::class,
                'posts.grid'
            );
        }

        return $layout->getBlock('posts.grid')->toHtml();
    }
}
