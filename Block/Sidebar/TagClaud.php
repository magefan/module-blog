<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Blog tag claud sidebar block
 */
class TagClaud extends \Magento\Framework\View\Element\Template
{
    use Widget;

    /**
     * Path to tag cloud 3D animation configuration
     */
    const ANIMATED_ENABLED = 'mfblog/sidebar/tag_claud/animated';
    const TEXT_HIGHLIGHT_COLOR = 'mfblog/sidebar/tag_claud/text_highlight_color';
    const CLOUD_HEIGHT = 'mfblog/sidebar/tag_claud/cloud_height';
    const TAG_COUNT = 'mfblog/sidebar/tag_claud/tag_count';

    /**
     * @var string
     */
    protected $_widgetKey = 'tag_claud';

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $_tagCollectionFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Tag\Collection
     */
    protected $_tags;

    /**
     * @var int
     */
    protected $_maxCount;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory $_tagCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_tagCollectionFactory = $tagCollectionFactory;
    }

    /**
     * Retrieve tags
     * @return array
     */
    public function getTags()
    {
        if ($this->_tags === null) {
            $this->_tags = $this->_tagCollectionFactory->create()
                ->addActiveFilter();

            $resource = $this->_tags->getResource();
                $this->_tags->getSelect()->joinLeft(
                    ['pt' => $resource->getTable('magefan_blog_post_tag')],
                    'main_table.tag_id = pt.tag_id',
                    []
                )->joinLeft(
                    ['p' => $resource->getTable('magefan_blog_post')],
                    'p.post_id = pt.post_id',
                    []
                )->joinLeft(
                    ['ps' => $resource->getTable('magefan_blog_post_store')],
                    'p.post_id = ps.post_id',
                    ['count' => 'count(main_table.tag_id)']
                )->group(
                    'main_table.tag_id'
                )->where(
                    'ps.store_id IN (?)',
                    [0, (int)$this->_storeManager->getStore()->getId()]
                )->where(
                    'main_table.is_active = ?',
                    \Magefan\Blog\Model\Tag::STATUS_ENABLED
                )->order('count DESC');

            $this->_tags->setPageSize($this->getConfigValue(self::TAG_COUNT) ?: 100);
        }

        return $this->_tags;
    }

    /**
     * Retrieve max tag number
     * @return array
     */
    public function getMaxCount()
    {
        if ($this->_maxCount == null) {
            $this->_maxCount = 0;
            foreach ($this->getTags() as $tag) {
                $count = $tag->getCount();
                if ($count > $this->_maxCount) {
                    $this->_maxCount = $count;
                }
            }
        }
        return $this->_maxCount;
    }

    /**
     * Retrieve tag class
     * @return array
     */
    public function getTagClass($tag)
    {
        $maxCount = $this->getMaxCount();
        $percent = floor(($tag->getCount() / $maxCount) * 100);

        if ($percent < 20) {
            return 'smallest';
        }
        if ($percent >= 20 && $percent < 40) {
            return 'small';
        }
        if ($percent >= 40 && $percent < 60) {
            return 'medium';
        }
        if ($percent >= 60 && $percent < 80) {
            return 'large';
        }
        return 'largest';
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getConfigValue(self::ANIMATED_ENABLED)) {
            $this->setTemplate('Magefan_Blog::sidebar/tag_claud_animated.phtml');
        }

        return $this;
    }

    /**
     * @return false|string
     */
    public function getAnimationConfig()
    {
        $color = $this->getConfigValue(self::TEXT_HIGHLIGHT_COLOR);
        $color = '#' . $this->escapeHtml(trim($color, '#'));
        $data = [
            'textColour' => $color,
            'outlineColour' => $color,
            'maxSpeed' => 0.03,
            'depth' => 0.75,
            'weight' => true,
            'initial' => [0, 1]
        ];

        foreach ($this->getData() as $key => $value) {
            if (strpos($key, 'animation_') === 0) {
                $key = str_replace('animation_', '', $key);
                $data[$key] = $value;
            }
        }

        return json_encode($data);
    }
}
