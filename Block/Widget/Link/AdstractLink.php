<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Widget\Link;

use Magento\Framework\View\Element\Template;
use Magefan\Blog\Model\Url;

/**
 * Class Link
 */
abstract class AdstractLink extends \Magento\Framework\View\Element\Html\Link implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var string
     */
    protected $_href;

    /**
     * @var string
     */
    protected $_anchorText;

    /**
     * @var Url
     */
    protected $urlFinder;

    /**
     * @var null
     */
    protected $className;

    /**
     * AdstractLink constructor.
     * @param Template\Context $context
     * @param Url $urlFinder
     * @param null $className
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $urlFinder,
        $className = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlFinder = $urlFinder;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        if ($this->_href === null) {
            if (!$this->getData('identifier')) {
                throw new \RuntimeException('Parameter post_identifier is not set.');
            }
            $href = false;

            $href = $this->urlFinder->getUrlPath($this->getData('identifier'), $this->className);

            $this->_href = $href;
        }
        return $this->_href;
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        if (!$this->_anchorText) {
            if ($this->getData('anchor_text')) {
                $this->_anchorText = $this->getData('anchor_text');
            } else {
                $this->_anchorText = 'Link';
            }
        }

        return $this->_anchorText;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if($this->getHref()) {
            return parent::_toHtml();
        }
        return '';
    }
}
