<?php

namespace Magefan\Blog\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magefan\Blog\Block\Adminhtml\Widget\FeaturedWidgetChooser;
class ConfigBlock extends \Magento\Config\Block\System\Config\Form\Field
{
    private $block;
    private $element;
   public function __construct(Context $context,FeaturedWidgetChooser $block,array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
   {
       $this->block = $block;
       parent::__construct($context, $data, $secureRenderer);
   }

   public function toHtml()
   {
       if (!$this->getLayout()->getBlock('post_ids_grid')) {

           $this->getLayout()->createBlock(\Magefan\Blog\Block\Adminhtml\Button::class,'post_ids_grid')->setTemplate('Magefan_Blog::buttonTemp.phtml');
       }
       return $this->getLayout()->getBlock('post_ids_grid')->toHtml();
   }

   public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
   {
       $this->element = $element;
       $columns = $this->getRequest()->getParam('website') || $this->getRequest()->getParam('store') ? 5 : 4;
       return $this->_decorateRowHtml($element, "<td colspan='{$columns}'>" . $this->toHtml() . '<div id="tyty"></div>');
   }
}