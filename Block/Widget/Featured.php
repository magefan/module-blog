<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Widget;

use Magefan\Blog\Block\Post\PostList\AbstractList;

/**
 * Blog featured posts widget
 */
class Featured extends AbstractList implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?: '';
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->addPostsFilter(
            $this->getPostIdsConfigValue()
        );

        $ids = [];
        foreach (explode(',', $this->getPostIdsConfigValue()) as $id) {
            $id = (int)trim($id);
            if ($id) {
                $ids[] = $id;
            }
        }

        if ($ids) {
            $ids = implode(',', $ids);
            $this->_postCollection->getSelect()->order(
                new \Zend_Db_Expr('FIELD(`main_table`.`post_id`,' . $ids .')')
            );
        }
    }

    /**
     * Retrieve post ids string
     *
     * @return string
     */
    protected function getPostIdsConfigValue(): string
    {
        return (string)$this->getData('posts_ids');
    }

    /**
     * Retrieve post short content
     *
     * @param  \Magefan\Blog\Model\Post $post
     * @param  mixed $len
     * @param  mixed $endCharacters
     * @return string
     */
    public function getShorContent($post, $len = null, $endCharacters = null)
    {
        return $post->getShortFilteredContent($len, $endCharacters);
    }

    /**
     * Retrieve template file path
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->_template) {
            return parent::getTemplate();
        }

        $defaultTemplate = 'Magefan_Blog::widget/recent.phtml';
        $templateType = $this->getData('template_type');
        if (!$templateType || 'custom_template' === $templateType) {
            $this->_template = $this->getData('custom_template') ?: $defaultTemplate;
        } elseif ($template = $this->templatePool->getTemplate('blog_post_list', $templateType)) {
            $this->_template = $template;
        } else {
            $this->_template = $defaultTemplate;
        }

        return parent::getTemplate();
    }
}
