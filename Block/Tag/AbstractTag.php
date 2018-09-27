<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Tag;

/**
 * Blog tag abstract block
 */
abstract class AbstractTag extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context

     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magefan\Blog\Model\Url $url
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magefan\Blog\Model\Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_url = $url;
    }

    /**
     * Retrieve tag instance
     *
     * @return \Magefan\Blog\Model\Tag
     */
    public function getTag()
    {
        return $this->_coreRegistry->registry('current_blog_tag');
    }

    /**
     * Retrieve tag content
     *
     * @return string
     */
    public function getContent()
    {
        $tag = $this->getTag();
        $key = 'filtered_content';
        if (!$tag->hasData($key)) {
            $cotent = $this->_filterProvider->getPageFilter()->filter(
                $tag->getContent()
            );
            $tag->setData($key, $cotent);
        }
        return $tag->getData($key);
    }

    public function getIdentities()
    {
        return $this->getTag()->getIdentities();
    }
}
