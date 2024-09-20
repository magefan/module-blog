<?php

namespace Magefan\Blog\Block\Adminhtml\Grid\Column\Render;

use Magefan\Blog\Model\Url;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Image extends AbstractRenderer
{

    /**
     * @param Url $url
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Url $url,
        Context $context,
        array $data = []
    ) {
        $this->_url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $imagePath = $row->getData($this->getColumn()->getIndex());
        if ($imagePath) {
            return '<img src="' .  $this->escapeHtml($this->_url->getMediaUrl($imagePath)) . '" alt="" width="75"/>';
        }
        return '';
    }
}
