<?php

namespace Magefan\Blog\Block\Adminhtml\Widget;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;

class FeaturedWidgetChooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Helper\SecureHtmlRenderer
     */
     protected $secureHtmlRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $collectionFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\View\Helper\SecureHtmlRenderer $secureHtmlRenderer,
        array $data = []
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->blockFactory = $blockFactory;
        $this->secureHtmlRenderer = $secureHtmlRenderer;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('post_ids');
        $this->setDefaultSort('post_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('blog/block_featuredwidget/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Chooser::class
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
     * Prepare Cms static blocks collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());
        return parent::_prepareCollection();
    }


//    /**
//     * Grid Row JS Callback
//     *
//     * @return string
//     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
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
                
                var currentState = '.
                $chooserJsObject .
                '.getElementValue().replace(/^\s+|\s+$/g,"").split(",");
                
                var isRepresent = function(Array,character) {
                        for (var i = 0; i < Array.length; i++) {
                            if (Array[i] === character) {
                            return i;
                        }
                    }
                    return -1;
                };
                
                var isEmpty = (currentState.length === 1) && (currentState[0] === "");
                
                if (eventElement.tagName === "LABEL" &&
                    trElement.querySelector("#" + eventElement.htmlFor) &&
                    trElement.querySelector("#" + eventElement.htmlFor).type === "checkbox"
                ) {
                    event.stopPropagation();
                    trElement.querySelector("#" + eventElement.htmlFor).trigger("click");
                }
                
                if (trElement && !isInputPosition) {
                    checkbox = Element.getElementsBySelector(trElement, "input");
                  
                    if (checkbox[0]) {
                        checked = isInputCheckbox ? checkbox[0].checked : !checkbox[0].checked;
                        if (checked) {
                            if (!isEmpty) {
                                var index = isRepresent(currentState,blockTitle);
                                if (index === -1) {
                                    currentState.push(blockTitle);
                                }
                                blockTitle = currentState.join(",");
                            }
                        }
                        else {
                          if (!isEmpty) {
                                var index = isRepresent(currentState,blockTitle);
                                if (index !== -1) {
                                currentState.splice(index, 1)
                                }
                            blockTitle = currentState.join(",");
                    }
                        }
                        grid.reloadParams = {
                            "selected_products[]": currentState
                        };
                        grid.setCheckboxChecked(checkbox[0], checked);
                    }
                }
          
                ' .
            $chooserJsObject .
            '.setElementValue(blockTitle);
                ' .
            $chooserJsObject .
            '.setElementLabel(blockTitle);
            }
        ';
        return $js;
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
            return 'function (grid, element,checked) {
                    var currentState = '.
                    $this->getId() . '
                    .getElementValue().replace(/^\s+|\s+$/g,"").split(",");
                    var isRepresent = function(Array,character) {
                        for (var i = 0; i < Array.length; i++) {
                            if (Array[i] === character) {
                            return i;
                        }
                    }
                    return -1;
                    };
                  
                        var index = isRepresent(currentState,element.value);
                        if(checked) {
                            if (index === -1 && element.value !== "on") {
                                currentState.push(element.value);
                            }
                        }
                        else {
                             if (index !== -1) {
                                currentState.splice(index, 1);
                            }
                        }
                    
           
                      grid.reloadParams = {
                            "selected_products[]": currentState
                        };
//                        console.log(grid.reloadParams);
                         var blockTitle = currentState.join(",");
                        ' .
                $this->getId() .
                '.setElementValue(blockTitle);
                ' .
                $this->getId() .
                '.setElementLabel(blockTitle);
                  
            }';
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'post_id_checkbox') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('post_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('post_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare columns for Cms blocks grid
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        //($this->getNameInLayout());exit;
        $this->addColumn(
            'post_id_checkbox',
            [
                'type' => 'checkbox',
                'name' => 'post_id_checkbox',
                'values' => $this->_getSelectedProducts(),
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


//        $this->addColumn(
//            'chooser_identifier',
//            ['header' => __('Identifier'), 'align' => 'left', 'index' => 'identifier']
//        );
//
//        $this->addColumn(
//            'chooser_is_active',
//            [
//                'header' => __('Status'),
//                'index' => 'is_active',
//                'type' => 'options',
//                'options' => [0 => __('Disabled'), 1 => __('Enabled')]
//            ]
//        );

        return parent::_prepareColumns();
    }



    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('blog/block_featuredwidget/chooser', ['_current' => true]);
    }


    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $selectedPosts = $this->getRequest()->getParam('selected_products');
        if ($selectedPosts !== null) {
            return array_values($selectedPosts);
        }
        return [];
    }
}