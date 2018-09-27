<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
}
