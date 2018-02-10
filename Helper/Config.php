<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Helper;

use Magento\Framework\App\Action\Action;

/**
 * Magefan Blog Config Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
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
     * Blog homepage display mode
     */
    const XML_PATH_HOMEPAGE_DISPLAY_MODE = 'mfblog/index_page/display_mode';

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
}
