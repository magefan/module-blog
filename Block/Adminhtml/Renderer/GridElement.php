<?php

namespace Magefan\Blog\Block\Adminhtml\Renderer;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magefan\Blog\Block\Adminhtml\Renderer\Grid;
use Magento\Framework\View\LayoutFactory;

class GridElement extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var Grid
     */
    private $grid;

    private $layoutF;

    /**
     * @param Factory                 $factoryElement
     * @param CollectionFactory       $factoryCollection
     * @param Escaper                 $escaper
     * @param $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null             $random
     * @param Grid                    $grid
     */
    public function __construct(
        Factory             $factoryElement,
        CollectionFactory   $factoryCollection,
        Escaper             $escaper,
        Grid                $grid,
        LayoutFactory       $layoutFactory,
        array               $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random             $random = null
    ) {
        $this->grid = $grid;
        $this->layoutF = $layoutFactory;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getElementHtml() : string
    {
        $layout = $this->layoutF->create();
        if(!$layout->getBlock('posts_grid')) {
            $layout->createBlock(
                Grid::class,
                'posts.grid'
            );
        }

        return $layout->getBlock('posts.grid')->toHtml();
    }
}