<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

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
    public const XML_PATH_EXTENSION_ENABLED = 'mfblog/general/enabled';
    public const XML_PATH_BLOG_DESIGN_VERSION = 'mfblog/general/design_version';
    public const GUEST_COMMENT = 'mfblog/post_view/comments/guest_comments';
    public const NUMBER_OF_COMMENTS = 'mfblog/post_view/comments/number_of_comments';
    public const NUMBER_OF_REPLIES = 'mfblog/post_view/comments/number_of_replies';
    public const COMMENT_STATUS = 'mfblog/post_view/comments/default_status';

    /**
     * Show top menu item config path
     */
    public const XML_PATH_TOP_MENU_SHOW_ITEM = 'mfblog/top_menu/show_item';

    public const XML_PATH_DISPLAY_CANONICAL_TAG_FOR = 'mfblog/seo/use_canonical_meta_tag_for';
    public const CANONICAL_PAGE_TYPE_NONE = 'none';
    public const CANONICAL_PAGE_TYPE_ALL = 'all';
    public const CANONICAL_PAGE_TYPE_INDEX = 'index';
    public const CANONICAL_PAGE_TYPE_POST = 'post';
    public const CANONICAL_PAGE_TYPE_CATEGORY = 'category';
    public const CANONICAL_PAGE_TYPE_AUTHOR = 'author';
    public const CANONICAL_PAGE_TYPE_ARCHIVE = 'archive';
    public const CANONICAL_PAGE_TYPE_TAG = 'tag';

    /**
     * Blog homepage title
     */
    public const XML_PATH_HOMEPAGE_TITLE = 'mfblog/index_page/title';

    /**
     * Blog homepage display mode
     */
    public const XML_PATH_HOMEPAGE_DISPLAY_MODE = 'mfblog/index_page/display_mode';

    /**
     * Blog homepage display mode
     */
    public const XML_PATH_HOMEPAGE_POSTS_SORT_BY = 'mfblog/index_page/posts_sort_by';

    /**
     * Blog homepage featured post ids
     */
    public const XML_PATH_HOMEPAGE_FEATURED_POST_IDS = 'mfblog/index_page/post_ids';

    /**
     * Top menu item text config path
     */
    public const XML_PATH_TOP_MENU_ITEM_TEXT = 'mfblog/top_menu/item_text';

    /**
     * Redirect to no slash config path
     */
    public const XML_PATH_REDIRECT_TO_NO_SLASH = 'mfblog/permalink/redirect_to_no_slash';

    /**
     * Page pagination type
     */
    public const XML_PATH_PAGE_PAGINATION_TYPE = 'mfblog/advanced_permalink/page_pagination_type';

    /**
     * Redirect to no slash config path (blog+)
     */
    public const XML_PATH_REDIRECT_TO_NO_SLASH_BLOG_PLUS = 'mfblog/advanced_permalink/redirect_to_no_slash';

    /**
     * Enabled advanced permalink
     */
    public const XML_PATH_ADVANCED_PERMALINK_ENABLED = 'mfblog/advanced_permalink/enabled';

    /**
     * Enabled RSS Feed
     */
    public const XML_PATH_RSS_FEED_ENABLED = 'mfblog/sidebar/rss_feed/enabled';

    /**
     * Top menu include categories config path
     */
    public const XML_PATH_TOP_MENU_INCLUDE_CATEGORIES = 'mfblog/top_menu/include_categories';

    /**
     * Top menu max depth config path
     */
    public const XML_PATH_TOP_MENU_MAX_DEPTH = 'mfblog/top_menu/max_depth';

    public const XML_RELATED_POSTS_ENABLED = 'mfblog/post_view/related_posts/enabled';
    public const XML_RELATED_POSTS_NUMBER = 'mfblog/post_view/related_posts/number_of_posts';

    public const XML_RELATED_PRODUCTS_ENABLED = 'mfblog/post_view/related_products/enabled';
    public const XML_RELATED_PRODUCTS_NUMBER = 'mfblog/post_view/related_products/number_of_products';

    public const XML_TAG_ROBOTS = 'mfblog/tag/robots';
    public const XML_SEARCH_ROBOTS = 'mfblog/search/robots';
    public const XML_AUTHOR_ROBOTS = 'mfblog/author/robots';

    /**
     * Blog CSS include config path
     */

    public const XML_INCLUDE_BLOG_CSS_ALL_PAGES = 'mfblog/developer/css_settings/include_all_pages';
    public const XML_INCLUDE_BLOG_CSS_HOME_PAGE = 'mfblog/developer/css_settings/include_home_page';
    public const XML_INCLUDE_BLOG_CSS_PRODUCT_PAGES = 'mfblog/developer/css_settings/include_product_page';
    public const XML_BLOG_CUSTOM_CSS = 'mfblog/developer/css_settings/custom_css';

    public const XML_BLOG_INCLUDE_BOOTSTRAP_CUSTOM_MINI = 'mfblog/developer/css_settings/include_bootstrap_custom_mini';

    public const XML_PATH_DESIGN_MODIFIED_DATE = 'mfblog/design/modified_date';
    public const XML_PATH_DESIGN_FORMAT_DATE_MODIFIED = 'mfblog/design/format_date_modified';

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
     * @param mixed $storeId
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
     * @param mixed $storeId
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
     * @param mixed $storeId
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
     * @param mixed $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve true if blog related posts are enabled
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isRelatedPostsEnabled($storeId = null): bool
    {
        return $this->isEnabled($storeId) && (bool)$this->getConfig(
            self::XML_RELATED_POSTS_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve true if blog related products are enabled
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isRelatedProductsEnabled($storeId = null): bool
    {
        return $this->isEnabled($storeId) && (bool)$this->getConfig(
            self::XML_RELATED_PRODUCTS_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve related products count
     *
     * @param mixed $storeId
     * @return int
     */
    public function getRelatedProductsCount($storeId = null): int
    {
        return (int)$this->getConfig(self::XML_RELATED_PRODUCTS_NUMBER, $storeId);
    }

    /**
     * Retrieve store config value
     *
     * @param string $path
     * @param mixed $storeId
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
     * Check if the canonical tag should be displayed for a given page type
     *
     * @param string $pageType
     * @return bool
     */
    public function getDisplayCanonicalTag($pageType): bool
    {

        if ($this->getConfig(self::XML_PATH_DISPLAY_CANONICAL_TAG_FOR)) {
            $displayFor = explode(',', $this->getConfig(self::XML_PATH_DISPLAY_CANONICAL_TAG_FOR));
        } else {
            $displayFor = [];
        }

        return in_array($pageType, $displayFor) || in_array(self::CANONICAL_PAGE_TYPE_ALL, $displayFor) ? true : false;
    }

    /**
     * Check if blog css should be included on all pages
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isBlogCssIncludeOnAll($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_INCLUDE_BLOG_CSS_ALL_PAGES,
            $storeId
        );
    }

    /**
     * Check if blog css should be included on home page
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isBlogCssIncludeOnHome($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_INCLUDE_BLOG_CSS_HOME_PAGE,
            $storeId
        );
    }

    /**
     * Check if blog css should be included on product pages
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isBlogCssIncludeOnProduct($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_INCLUDE_BLOG_CSS_PRODUCT_PAGES,
            $storeId
        );
    }

    /**
     * Retrieve page pagination type
     *
     * @param int|null $storeId
     * @return string
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
     * Retrieve custom CSS configuration
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCustomCss($storeId = null): string
    {
        return (string)$this->getConfig(self::XML_BLOG_CUSTOM_CSS, $storeId);
    }

    /**
     * Retrieve include bootstrap custom mini configuration
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getIncludeBootstrapCustomMini($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_BLOG_INCLUDE_BOOTSTRAP_CUSTOM_MINI,
            $storeId
        );
    }

    /**
     * Retrieve blog design version
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDesignVersion($storeId = null): string
    {
        return (string)$this->getConfig(
            self::XML_PATH_BLOG_DESIGN_VERSION,
            $storeId
        );
    }

    /**
     * Check if modified date display is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isModifiedDateEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_DESIGN_MODIFIED_DATE, $storeId);
    }

    /**
     * Retrieve modified date format
     *
     * @param int|null $storeId
     * @return string
     */
    public function getModifiedDateFormat($storeId = null): string
    {
        return (string)$this->getConfig(self::XML_PATH_DESIGN_FORMAT_DATE_MODIFIED, $storeId);
    }

    /**
     * Check if RSS feed is enabled
     *
     * @param int|null $storeId
     * @return string
     */
    public function isRssFeedEnabled($storeId = null): string
    {
        return (string)$this->getConfig(
            self::XML_PATH_RSS_FEED_ENABLED,
            $storeId
        );
    }
}
