<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magefan\Blog\Model\Url;

/**
 * Class Link
 */
class Link extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    const CLASS_AUTHOR = 'author';
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
    protected $modelRepository;

    /**
     * @var null
     */
    protected $model = null;

    /**
     * AdstractLink constructor.
     * @param Template\Context $context
     * @param Url $urlFinder
     * @param null $modelRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $urlFinder,
        $modelRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlFinder = $urlFinder;
        $this->modelRepository = $modelRepository;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        if ($this->_href === null && $this->model) {
            if ($this->model->getData('identifier') && $this->model->getControllerName() != self::CLASS_AUTHOR && $this->model->getControllerName()) {
                $this->_href = $this->urlFinder->getUrlPath($this->model->getData('identifier'), $this->model->getControllerName());
            } elseif ($this->model->getControllerName() == self::CLASS_AUTHOR) {
                $this->_href = $this->model->getUrl();
            }
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
            } elseif ($this->model && $this->model->getControllerName() != self::CLASS_AUTHOR && $this->model->getData('title')) {
                $this->_anchorText = $this->model->getData('title');
            } elseif ($this->model && $this->model->getData('meta-title')) {
                $this->_anchorText = $this->model->getData('meta-title');
            } elseif ($this->model->getControllerName() == self::CLASS_AUTHOR) {
                $this->_anchorText = $this->model->getTitle();
            }
        }
        return $this->_anchorText;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        try {
            if ($this->getData('entity_id')) {
                $this->model = $this->getRepository()->getbyId($this->getData('entity_id'));
            }
            if (!$this->getHref()) {
                return '<b>' . $this->escapeHtml($this->getLabel()) . '</b>';
            } else {
                return '<a href="' . $this->getHref() . '" title="' . $this->getData('anchor_title') . '">' . $this->escapeHtml($this->getLabel()) . '</a>';
            }
            return '';

        } catch (NoSuchEntityException $e) {
            return '';
//            return '<b>There is no object with such link</b>';
        }
    }

    /**
     * @return \Magento\Framework\App\ObjectManager
     */
    private function ObjectManager()
    {
        return $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @return mixed|null
     */
    private function getRepository()
    {
        if (is_array($this->modelRepository)) {
            $this->modelRepository = $this->ObjectManager()->get($this->modelRepository['instance']);
        }
        return $this->modelRepository;
    }

}
