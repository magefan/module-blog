<?php

namespace Magefan\Blog\Block\Adminhtml\Renderer;

use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as WidgetCollectionFactory;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Grid  extends \Magento\Backend\Block\Widget\Grid\Extended
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
            'blog/block_featuredwidget/chooser', ['uniq_id' => $uniqId,'instance_id' =>
                (int)$this->getRequest()->getParam('instance_id')]
        );

        $chooser = $this->getLayout()->createBlock(
            Chooser::class
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
     * @return FeaturedWidgetChooser
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
         element.checked = true;
                grid.reloadParams = {
                            "selected_products[]": window.postState
                        };
                     
              }
       ';
    }

    public function getPostIdsFromConfig() {
        return (string)$this->_scopeConfig->getValue(
            'mfblog/sidebar/featured_posts/posts_ids',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
                  
                    grid.reloadParams = {
                        "selected_products[]": window.postState
                    };    
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
     * @return FeaturedWidgetChooser
     * @throws \Exception
     */
    protected function _prepareColumns() : Grid
    {
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

        return parent::_prepareColumns();
    }



    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() : string
    {
        return $this->getUrl('blog/block_featuredwidget/chooser', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts() : array
    {
        $selectedPosts = $this->getRequest()->getParam('selected_products');

        if ($selectedPosts !== null) {
            return array_values($selectedPosts);
        }

        $postIds = $this->getPostIdsFromConfig();
        return explode(',',$postIds);
    }
}