<?php

namespace Magefan\Blog\Block\Adminhtml\Widget;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as WidgetCollectionFactory;
use Magento\Framework\Registry;

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
     * @var WidgetCollectionFactory
     */
     protected $widgetCollectionFactory;

    /**
     * @var Registry
     */
     protected $registry;

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
        WidgetCollectionFactory $widgetCollectionFactory,
        Registry $registry,
        array $data = []
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->blockFactory = $blockFactory;
        $this->secureHtmlRenderer = $secureHtmlRenderer;
        $this->widgetCollectionFactory = $widgetCollectionFactory;
        $this->registry = $registry;
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
        $this->widgetInstanceId = (string)$this->getRequest()->getParam('instance_id');

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
        $sourceUrl = $this->getUrl('blog/block_featuredwidget/chooser', ['uniq_id' => $uniqId,'instance_id' =>
            (string)$this->getRequest()->getParam('instance_id')]);

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
//        if (!$this->registry->registry('selected_products')) {
            $this->setDefaultFilter(['post_id_checkbox' => 1]);
//        }
        $this->setCollection($this->collectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * @return string
     */
    public function getRowInitCallback() : string
    {
       return 'function (grid, element,checked) {
                    grid.reloadParams = {
                            "selected_products[]": window.postState
                    };
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
                       
                        grid.reloadParams = {
                            "selected_products[]": window.postState
                        };
                        
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
    public function getCheckboxCheckCallback()
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
                  
                    grid.reloadParams = {
                        "selected_products[]": window.postState
                    };    
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
        //var_dump(get_class_methods($this));exit;
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
    protected function _getSelectedProducts() : array
    {
        $selectedPosts = $this->getRequest()->getParam('selected_products');
        $selectedPostsFromRegistry = $this->registry->registry('selected_products');
        //var_dump($this->registry->registry('selected_products'));exit;

        if ($selectedPostsFromRegistry !== null) {
            return array_values($selectedPostsFromRegistry);
        }

        if ($selectedPosts !== null) {
            $this->registry->register('selected_products',$selectedPosts);
            return array_values($selectedPosts);
        }

        $widgetCollection = $this->widgetCollectionFactory->create()->addFieldToFilter('instance_id',['eq' =>
            (int)$this->getRequest()->getParam('instance_id')]);

        if (count($widgetCollection) === 1) {
            $widget = $widgetCollection->getFirstItem();
            $widgetParameters = $widget->getWidgetParameters();
            if (isset($widgetParameters['posts_ids'])) {
                return explode(',',(string)$widgetParameters['posts_ids']);
            }
        }
        return [];
    }
}