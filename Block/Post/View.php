<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post view
 */
class View extends AbstractPost implements \Magento\Framework\DataObject\IdentityInterface
{

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $i =  $this->getPost()->getIdentities();
        var_dump($i);
        echo self::printDebugBacktrace($this->getNameInLayout());

        return $i;
    }

    public static function printDebugBacktrace($title = 'Debug Backtrace:') {
        $output     = "";
        $output .= "<hr /><div>" . $title . '<br /><table border="1" cellpadding="2" cellspacing="2">';

        $stacks     = debug_backtrace();

        $output .= "<thead><tr><th><strong>File</strong></th><th><strong>Line</strong></th><th><strong>Function</strong></th>".
            "</tr></thead>";
        foreach($stacks as $_stack)
        {
            if (!isset($_stack['file'])) $_stack['file'] = '[PHP Kernel]';
            if (!isset($_stack['line'])) $_stack['line'] = '';

            $output .=  "<tr><td>{$_stack["file"]}</td><td>{$_stack["line"]}</td>".
                "<td>{$_stack["function"]}</td></tr>";
        }
        $output .=  "</table></div><hr /></p>";
        return $output;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $post = $this->getPost();
        if ($post) {
            $this->_addBreadcrumbs($post->getTitle(), 'blog_post');
            $this->pageConfig->addBodyClass('blog-post-' . $post->getIdentifier());
            $this->pageConfig->getTitle()->set($post->getMetaTitle());
            $this->pageConfig->setKeywords($post->getMetaKeywords());
            $this->pageConfig->setDescription($post->getMetaDescription());
            $this->pageConfig->addRemotePageAsset(
                $post->getCanonicalUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $this->escapeHtml($post->getTitle())
                );
            }

            if ($post->getIsPreviewMode()) {
                $this->pageConfig->setRobots('NOINDEX,FOLLOW');
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param  string $title
     * @param  string $key
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($title = null, $key = null)
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $blogTitle = $this->_scopeConfig->getValue(
                'mfblog/index_page/title',
                ScopeInterface::SCOPE_STORE
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => __($blogTitle),
                    'title' => __($blogTitle),
                    'link' => $this->_url->getBaseUrl()
                ]
            );

            $parentCategories = [];
            $parentCategory = $this->getPost()->getParentCategory();
            while ($parentCategory) {
                $parentCategories[] = $parentCategory;
                $parentCategory = $parentCategory->getParentCategory();
            }

            for ($i = count($parentCategories) - 1; $i >= 0; $i--) {
                $parentCategory = $parentCategories[$i];
                $breadcrumbsBlock->addCrumb('blog_parent_category_' . $parentCategory->getId(), [
                    'label' => $parentCategory->getTitle(),
                    'title' => $parentCategory->getTitle(),
                    'link'  => $parentCategory->getCategoryUrl()
                ]);
            }

            $breadcrumbsBlock->addCrumb($key, [
                'label' => $title ,
                'title' => $title
            ]);
        }
    }
}
