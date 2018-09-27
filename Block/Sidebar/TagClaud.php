<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog tag claud sidebar block
 */
class TagClaud extends \Magento\Framework\View\Element\Template
{
    use Widget;

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
                );
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
        if ($percent >= 20 and $percent < 40) {
            return 'small';
        }
        if ($percent >= 40 and $percent < 60) {
            return 'medium';
        }
        if ($percent >= 60 and $percent < 80) {
            return 'large';
        }
        return 'largest';
    }
}
