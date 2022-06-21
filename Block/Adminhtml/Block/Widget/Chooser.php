<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Block\Widget;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Magefan Blog post chooser for Featured Blog Posts widget
 */
class Chooser extends Extended
{
    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param BlockFactory $blockFactory
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        BlockFactory $blockFactory,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->blockFactory = $blockFactory;
        $this->collectionFactory = $collectionFactory;
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
        $this->setDefaultSort('blog_posts');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
    }

    /**
     * @param AbstractElement $element
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('blog/block_widget/chooser', ['uniq_id' => $uniqId]);

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
        );

        if ($element->getValue()) {
            $block = $this->blockFactory->create()->load($element->getValue());
            if ($block->getId()) {
                $chooser->setLabel($this->escapeHtml($block->getTitle()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
            let trElement = Event.findElement(event, "tr");
            let postId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
            let postTitle = trElement.down("td").next().innerHTML;
            let values = trElement.down("td").next().innerHTML;
            let valuesInput = document.getElementById(grid.containerId + "value");
            let labels = document.getElementById(grid.containerId + "label");
            let ids = valuesInput.value;
            labels.style.display = "grid";

            if(ids.split(\',\').find(element => element === postId.toString()) === undefined) {
                if(document.querySelectorAll("#" + grid.containerId + "label" + " span").length === 0){
                    labels.textContent = "";
                }
                let postItem = document.createElement("span");
                let idsArray = valuesInput.value.split(\',\');
                postItem.id = "post-" + postId;
                postItem.textContent = postTitle;
                postItem.className = "post-item";
                postItem.onclick = function(post){
                    let val = document.getElementsByName("parameters[posts_ids]")[0];
                    let idsArray = val.value.split(\',\');
                    var postIndex = idsArray.indexOf(postId.toString());
                    if (postIndex !== -1) {
                        idsArray.splice(postIndex, 1);
                    }
                    valuesInput.value = idsArray.join();
                    post.target.remove();
                    if(document.querySelectorAll("#" + grid.containerId + "label" + " span").length === 0){
                        labels.textContent = "Not Selected";
                    }
                };
                labels.append(postItem);
                valuesInput.value = valuesInput.value + "," + postId;
            }else {
                alert("This post already selected!");
                return;
            }
                ' .
            $chooserJsObject .
            '.close();
            }
            ';
        return $js;
    }

    /**
     * Prepare Blog post collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for Blog post grid
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'chooser_id',
            ['header' => __('ID'), 'align' => 'right', 'index' => 'post_id', 'width' => 20]
        );

        $this->addColumn('chooser_identifier', ['header' => __('Title'), 'align' => 'left', 'index' => 'title']);

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('blog/block_widget/chooser', ['_current' => true]);
    }
}
