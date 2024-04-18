<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

class InfoBlogExtra extends InfoPlan
{

    /**
     * @return string
     */
    protected function getMinPlan(): string
    {
        return 'Extra';
    }

    /**
     * @return string
     */
    protected function getSectionId(): string
    {
        $sections = json_encode([
            'mfblog_post_view_category_posts',
            'mfblog_post_view_comments_enabled',
            'mfblog_post_view_comments_admin_enable_comment_notification',
            'mfblog_post_view_comments_email_template',
            'mfblog_post_view_comments_sender_form',
            'mfblog_post_view_comments_admin_send_from',
            'mfblog_post_view_comments_admin_send_to',
            'mfblog_post_view_comments_admin_email_template',
            'mfblog_blog_search',
            'mfblog_sidebar_contents'
        ]);
        return $sections;
    }
    protected function getText(): string
    {
        return 'This option is available in <strong>Extra</strong> plan only.';

    }
}
