<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Magefan Blog Config Model
 */
class Config
{
    /**
     * Extension enabled config path
     */
    const XML_PATH_EXTENSION_ENABLED = 'mfblog/general/enabled';
    const GUEST_COMMENT = 'mfblog/post_view/comments/guest_comments';
    const NUMBER_OF_COMMENTS = 'mfblog/post_view/comments/number_of_comments';
    const NUMBER_OF_REPLIES = 'mfblog/post_view/comments/number_of_replies';
    const COMMENT_STATUS = 'mfblog/post_view/comments/default_status';

    /**
     * Show top menu item config path
     */
    const XML_PATH_TOP_MENU_SHOW_ITEM = 'mfblog/top_menu/show_item';

    const XML_PATH_DISPLAY_CANONICAL_TAG_FOR = 'mfblog/seo/use_canonical_meta_tag_for';
    const CANONICAL_PAGE_TYPE_NONE = 'none';
    const CANONICAL_PAGE_TYPE_ALL = 'all';
    const CANONICAL_PAGE_TYPE_INDEX = 'index';
    const CANONICAL_PAGE_TYPE_POST = 'post';
    const CANONICAL_PAGE_TYPE_CATEGORY = 'category';
    const CANONICAL_PAGE_TYPE_AUTHOR = 'author';
    const CANONICAL_PAGE_TYPE_ARCHIVE = 'archive';
    const CANONICAL_PAGE_TYPE_TAG = 'tag';

    /**
     * Blog homepage title
     */
    const XML_PATH_HOMEPAGE_TITLE = 'mfblog/index_page/title';

    /**
     * Blog homepage display mode
     */
    const XML_PATH_HOMEPAGE_DISPLAY_MODE = 'mfblog/index_page/display_mode';

    /**
     * Blog homepage display mode
     */
    const XML_PATH_HOMEPAGE_POSTS_SORT_BY = 'mfblog/index_page/posts_sort_by';

    /**
     * Blog homepage featured post ids
     */
    const XML_PATH_HOMEPAGE_FEATURED_POST_IDS = 'mfblog/index_page/post_ids';

    /**
     * Top menu item text config path
     */
    const XML_PATH_TOP_MENU_ITEM_TEXT = 'mfblog/top_menu/item_text';

    /**
     * Redirect to no slash config path
     */
    const XML_PATH_REDIRECT_TO_NO_SLASH = 'mfblog/permalink/redirect_to_no_slash';

    /**
     * Page pagination type
     */
    const XML_PATH_PAGE_PAGINATION_TYPE = 'mfblog/advanced_permalink/page_pagination_type';

    /**
     * Redirect to no slash config path (blog+)
     */
    const XML_PATH_REDIRECT_TO_NO_SLASH_BLOG_PLUS = 'mfblog/advanced_permalink/redirect_to_no_slash';

    /**
     * Enabled advanced permalink
     */
    const XML_PATH_ADVANCED_PERMALINK_ENABLED = 'mfblog/advanced_permalink/enabled';

    /**
     * Top menu include categories config path
     */
    const XML_PATH_TOP_MENU_INCLUDE_CATEGORIES = 'mfblog/top_menu/include_categories';

    /**
     * Top menu max depth config path
     */
    const XML_PATH_TOP_MENU_MAX_DEPTH = 'mfblog/top_menu/max_depth';

    const XML_RELATED_POSTS_ENABLED = 'mfblog/post_view/related_posts/enabled';
    const XML_RELATED_POSTS_NUMBER = 'mfblog/post_view/related_posts/number_of_posts';

    const XML_RELATED_PRODUCTS_ENABLED = 'mfblog/post_view/related_products/enabled';
    const XML_RELATED_PRODUCTS_NUMBER = 'mfblog/post_view/related_products/number_of_products';

    const XML_TAG_ROBOTS = 'mfblog/tag/robots';
    const XML_SEARCH_ROBOTS = 'mfblog/search/robots';
    const XML_AUTHOR_ROBOTS = 'mfblog/author/robots';

    /**
     * Blog CSS include config path
     */

    const XML_INCLUDE_BLOG_CSS_ALL_PAGES = 'mfblog/developer/css_settings/include_all_pages';
    const XML_INCLUDE_BLOG_CSS_HOME_PAGE = 'mfblog/developer/css_settings/include_home_page';
    const XML_INCLUDE_BLOG_CSS_PRODUCT_PAGES = 'mfblog/developer/css_settings/include_product_page';
    const XML_BLOG_CUSTOM_CSS = 'mfblog/developer/css_settings/custom_css';

    const XML_BLOG_INCLUDE_BOOTSTRAP_CUSTOM_MINI = 'mfblog/developer/css_settings/include_bootstrap_custom_mini';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve author page robots
     *
     * @return string
     */
    public function getAuthorRobots($storeId = null)
    {
        return $this->getConfig(
            self::XML_AUTHOR_ROBOTS,
            $storeId
        );
    }

    /**
     * Retrieve tag page robots
     *
     * @return string
     */
    public function getTagRobots($storeId = null)
    {
        return $this->getConfig(
            self::XML_TAG_ROBOTS,
            $storeId
        );
    }

    /**
     * Retrieve search page robots
     *
     * @return string
     */
    public function getSearchRobots($storeId = null)
    {
        return $this->getConfig(
            self::XML_SEARCH_ROBOTS,
            $storeId
        );
    }

    /**
     * Retrieve true if blog module is enabled
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve true if blog related posts are enabled
     *
     * @return bool
     */
    public function isRelatedPostsEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_RELATED_POSTS_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve true if blog related products are enabled
     *
     * @return bool
     */
    public function isRelatedProductsEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_RELATED_PRODUCTS_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $pageType
     * @return bool
     */
    public function getDisplayCanonicalTag($pageType)
    {

        if ($this->getConfig(self::XML_PATH_DISPLAY_CANONICAL_TAG_FOR)) {
            $displayFor = explode(',', $this->getConfig(self::XML_PATH_DISPLAY_CANONICAL_TAG_FOR));
        } else {
            $displayFor = [];
        }

        return in_array($pageType, $displayFor) || in_array(self::CANONICAL_PAGE_TYPE_ALL, $displayFor) ? true : false;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isBlogCssIncludeOnAll($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_INCLUDE_BLOG_CSS_ALL_PAGES,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isBlogCssIncludeOnHome($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_INCLUDE_BLOG_CSS_HOME_PAGE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isBlogCssIncludeOnProduct($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_INCLUDE_BLOG_CSS_PRODUCT_PAGES,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getPagePaginationType($storeId = null)
    {
        if ($this->getConfig(self::XML_PATH_ADVANCED_PERMALINK_ENABLED, $storeId)) {
            return $this->getConfig(
                self::XML_PATH_PAGE_PAGINATION_TYPE,
                $storeId
            );
        }

        return 'page';
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getCustomCss($storeId = null): string
    {
        return (string)$this->getConfig(self::XML_BLOG_CUSTOM_CSS, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getIncludeBootstrapCustomMini($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_BLOG_INCLUDE_BOOTSTRAP_CUSTOM_MINI,
            $storeId
        );
    }
}
