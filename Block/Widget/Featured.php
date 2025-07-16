<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Widget;

/**
 * Blog featured posts widget
 */

class Featured extends \Magefan\Blog\Block\Sidebar\Featured implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Set blog template
     *
     * @return this
     */
    public function _toHtml()
    {
        $this->setTemplate(
            $this->getCustomTemplate()
        );

        return \Magento\Framework\View\Element\Template::_toHtml();
    }

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
     * Retrieve post ids string
     *
     * @return string
     */
    protected function getPostIdsConfigValue()
    {
        $postsIds = (string) $this->getData('posts_ids');
        $registeredPostsIds = $this->_coreRegistry->registry('posts_ids');

        if ($postsIds !== '' && $postsIds !== $registeredPostsIds) {
            if ($registeredPostsIds !== null) {
                $this->_coreRegistry->unregister('posts_ids');
            }
            $this->_coreRegistry->register('posts_ids', $postsIds);
        }

        return $this->_coreRegistry->registry('posts_ids');
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
     * Get relevant path to template
     * Skip parent one as it use template for sidebar block
     *
     * @return string
     */
    public function getTemplate()
    {
        return \Magefan\Blog\Block\Post\PostList\AbstractList::getTemplate();
    }

	/**
	 * @return mixed
	 */
    public function getElementClass(){
        return 'featured';
    }

    /**
     * @return string
     */
    public function getCustomTemplate() {
        $designVersion = $this->_scopeConfig->getValue('mfblog/design/version', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($designVersion === '2025-04' && $this->getTemplate() && strpos($this->getTemplate(), 'article.phtml') !== false) {
			return $this->getTemplate();
		}
        if ($this->getData('mf_template')) {
            if ($designVersion == '2025-04') {
                $this->setNewDesignType($this->getData('mf_template'));
                return 'Magefan_BlogExtra::widget/blog-widget-2025-04.phtml';
            }

            if ($template = $this->templatePool->getTemplate('blog_post_list', $this->getData('mf_template'))) {
                return $template;
            }
        } elseif ($this->getData('custom_template')) {
            return $this->getData('custom_template');
        }
        return 'Magefan_Blog::widget/recent.phtml';
    }
}
