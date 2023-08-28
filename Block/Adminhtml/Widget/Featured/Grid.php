<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Widget\Featured;

use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as WidgetCollectionFactory;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var PostCollectionFactoryy
     */
    protected $postCollectionFactory;

    /**
     * @var WidgetCollectionFactory
     */
     protected $widgetCollectionFactory;

    /**
     * @param Context                 $context
     * @param Data                    $backendHelper
     * @param PostCollectionFactory   $postCollectionFactory
     * @param WidgetCollectionFactory $widgetCollectionFactory
     * @param array                   $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        PostCollectionFactory $postCollectionFactory,
        WidgetCollectionFactory $widgetCollectionFactory,
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->widgetCollectionFactory = $widgetCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct() : void
    {
        parent::_construct();
        $this->setId('post_ids');
        $this->setDefaultSort('post_id');
        $this->setUseAjax(true);
    }

    /**
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element) : AbstractElement
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl(
            'blog/block_featured_grid/chooser',
            ['uniq_id' => $uniqId,'instance_id' =>
            (int)$this->getRequest()->getParam('instance_id')]
        );

        $chooser = $this->getLayout()->createBlock(
            \Magefan\Blog\Block\Adminhtml\Widget\Featured\Grid\Chooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        )->setChooserJsObject(
            $this->getId()
        )->setJsObjectName(
            $this->getJsObjectName()
        );

        if ($element->getValue()) {
            $chooser->setLabel($this->escapeHtml((string)$element->getValue()));
        }

        $element->setData('after_element_html', $chooser->toHtml());

        return $element;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection() : Grid
    {
        $this->setDefaultFilter(['post_id_checkbox' => 1]);
        $this->setCollection($this->postCollectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * @return string
     */
    public function getRowInitCallback() : string
    {
        return 'function (grid, element,checked) {
            if(window.needToReload){
                var currentState = ' .
                $this->getId() .
                '.getElementValue();

                if(!currentState) {
                    grid.reloadParams = {
                    "selected_posts[]": ["-1"]
                    };
                }
                else {
                    grid.reloadParams = {
                        "selected_posts[]": window.postState
                    };
                }

                grid.reload(grid.url);
                window.needToReload = false;
            }
          }
       ';
    }

    /**
     * @return string
     */
    public function getRowClickCallback() : string
    {
        return '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr"),
                eventElement = Event.element(event),
                isInputCheckbox = eventElement.tagName === "INPUT" && eventElement.type === "checkbox",
                isInputPosition = grid.targetElement &&
                    grid.targetElement.tagName === "INPUT" &&
                    grid.targetElement.name === "position",
                checked = false,
                checkbox = null;

                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                var blockTitle = trElement.down("td").next().innerHTML.replace(/^\s+|\s+$/g,"");

                var isRepresent = function(Array,character) {
                        for (var i = 0; i < Array.length; i++) {
                            if (Array[i] === character) {
                            return i;
                        }
                    }
                    return -1;
                };

                if (eventElement.tagName === "LABEL" &&
                    trElement.querySelector("#" + eventElement.htmlFor) &&
                    trElement.querySelector("#" + eventElement.htmlFor).type === "checkbox"
                ) {
                    event.stopPropagation();
                    trElement.querySelector("#" + eventElement.htmlFor).trigger("click");
                }

                if (trElement && !isInputPosition) {
                    checkbox = Element.getElementsBySelector(trElement, "input");
                    var index = isRepresent(window.postState,blockTitle);
                    if (checkbox[0]) {
                        checked = isInputCheckbox ? checkbox[0].checked : !checkbox[0].checked;
                        if (checked) {
                            if (index === -1) {
                                window.postState.push(blockTitle);
                            }
                        }
                        else {
                            if (index !== -1) {
                                window.postState.splice(index, 1);
                            }
                        }

                        if (window.postState.length) {
                            grid.reloadParams = {
                                "selected_posts[]": window.postState
                            };
                        }
                        else {
                            grid.reloadParams = {
                                "selected_posts[]": ["-1"]
                            };
                        }

                        grid.setCheckboxChecked(checkbox[0], checked);
                    }
                }
            }
        ';
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback() : string
    {
        return 'function (grid, element,checked) {
            var isRepresent = function(Array,character) {
                for (var i = 0; i < Array.length; i++) {
                    if (Array[i] === character) {
                    return i;
                }
            }
            return -1;
            };

            var index = isRepresent(window.postState,element.value);

            if(checked) {
                if (index === -1 && element.value !== "on") {
                    window.postState.push(element.value);
                }
            }
            else {
                if (index !== -1) {
                    window.postState.splice(index, 1);
                }
            }

            if (window.postState.length) {
                    grid.reloadParams = {
                        "selected_posts[]": window.postState
                    };
                }
            else {
                grid.reloadParams = {
                    "selected_posts[]": ["-1"]
                };
            }
        }';
    }

    /**
     * @param  $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column) : Grid
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'post_id_checkbox') {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('post_id', ['in' => $postIds]);
            } elseif (!empty($postIds)) {
                $this->getCollection()->addFieldToFilter('post_id', ['nin' => $postIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns() : Grid
    {
        $this->addColumn(
            'post_id_checkbox',
            [
                'type' => 'checkbox',
                'name' => 'post_id_checkbox',
                'values' => $this->_getSelectedPosts(),
                'index' => 'post_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'post_id',
            [
                'header' => __('Post Id'),
                'sortable' => true,
                'index' => 'post_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() : string
    {
        return $this->getUrl('blog/block_featured_grid/chooser', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedPosts() : array
    {
        $selectedPosts = $this->getRequest()->getParam('selected_posts');

        if ($selectedPosts !== null) {
            return array_values($selectedPosts);
        }

        $widgetCollection = $this->widgetCollectionFactory->create()->addFieldToFilter(
            'instance_id',
            ['eq' =>
            (int)$this->getRequest()->getParam('instance_id')]
        );

        if (count($widgetCollection) === 1) {
            $widget = $widgetCollection->getFirstItem();
            $widgetParameters = $widget->getWidgetParameters();
            if (isset($widgetParameters['posts_ids'])) {
                return explode(',', (string)$widgetParameters['posts_ids']);
            }
        }

        return [];
    }
}
