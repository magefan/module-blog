<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Import;

/**
 * Wordpress import model
 */
class Wordpress extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'dbhost'];

    public function execute()
    {
        $con = $this->_connect = mysqli_connect(
            $this->getData('dbhost'),
            $this->getData('uname'),
            $this->getData('pwd'),
            $this->getData('dbname')
        );

        if (mysqli_connect_errno()) {
            throw new \Exception("Failed connect to wordpress database", 1);
        }

        mysqli_set_charset($con, "utf8");

        $_pref = mysqli_real_escape_string($con, $this->getData('prefix'));

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.term_id as old_id,
                    t.name as title,
                    t.slug as identifier,
                    tt.parent as parent_id
                FROM '.$_pref.'terms t
                LEFT JOIN '.$_pref.'term_taxonomy tt on t.term_id = tt.term_id
                WHERE tt.taxonomy = "category" AND t.slug <> "uncategorized"';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare category data */
            foreach (['title', 'identifier'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

            $data['store_ids'] = [$this->getStoreId()];
            $data['is_active'] = 1;
            $data['position'] = 0;
            $data['path'] = 0;
            $data['identifier'] = $this->prepareIdentifier($data['identifier']);

            $category = $this->_categoryFactory->create();
            try {
                /* Initial saving */
                $category->setData($data)->save();
                $this->_importedCategoriesCount++;
                $categories[$category->getId()] = $category;
                $oldCategories[$category->getOldId()] = $category;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                unset($category);
                $this->_skippedCategories[] = $data['title'];
                $this->_logger->addDebug('Blog Category Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Reindexing parent categories */
        foreach ($categories as $ct) {
            if ($oldParentId = $ct->getData('parent_id')) {
                if (isset($oldCategories[$oldParentId])) {
                    $ct->setPath(
                        $parentId = $oldCategories[$oldParentId]->getId()
                    );
                }
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $changed = false;
            foreach ($categories as $ct) {
                if ($ct->getPath()) {
                    $parentId = explode('/', $ct->getPath())[0];
                    $pt = $categories[$parentId];
                    if ($pt->getPath()) {
                        $ct->setPath($pt->getPath() . '/'. $ct->getPath());
                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                break;
            }
        }
        /* end*/

        foreach ($categories as $ct) {
            /* Final saving */
            $ct->save();
        }

        /* Import tags */
        $tags = [];
        $oldTags = [];

        $sql = 'SELECT
                    t.term_id as old_id,
                    t.name as title,
                    t.slug as identifier,
                    tt.parent as parent_id
                FROM '.$_pref.'terms t
                LEFT JOIN '.$_pref.'term_taxonomy tt on t.term_id = tt.term_id
                WHERE tt.taxonomy = "post_tag" AND t.slug <> "uncategorized"';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare tag data */
            foreach (['title', 'identifier'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

            if ($data['title']{0} == '?') {
                /* fix for ???? titles */
                $data['title'] = $data['identifier'];
            }

            $data['identifier'] = $this->prepareIdentifier($data['identifier']);

            $tag = $this->_tagFactory->create();
            try {
                /* Initial saving */
                $tag->setData($data)->save();
                $this->_importedTagsCount++;
                $tags[$tag->getId()] = $tag;
                $oldTags[$tag->getOldId()] = $tag;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                unset($tag);
                $this->_skippedTags[] = $data['title'];
                $this->_logger->addDebug('Blog Tag Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Import posts */
        $sql = 'SELECT * FROM '.$_pref.'posts WHERE `post_type` = "post"';
        $result = $this->_mysqliQuery($sql);

        while ($data = mysqli_fetch_assoc($result)) {
            /* find post categories*/
            $postCategories = [];

            $sql = 'SELECT tt.term_id as term_id FROM '.$_pref.'term_relationships tr
                    LEFT JOIN '.$_pref.'term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.`object_id` = "'.$data['ID'].'" AND tt.taxonomy = "category"';

            $result2 = $this->_mysqliQuery($sql);
            while ($data2 = mysqli_fetch_assoc($result2)) {
                $oldTermId = $data2['term_id'];
                if (isset($oldCategories[$oldTermId])) {
                    $postCategories[] = $oldCategories[$oldTermId]->getId();
                }
            }

            /* find post tags*/
            $postTags = [];

            $sql = 'SELECT tt.term_id as term_id FROM '.$_pref.'term_relationships tr
                    LEFT JOIN '.$_pref.'term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.`object_id` = "'.$data['ID'].'" AND tt.taxonomy = "post_tag"';

            $result2 = $this->_mysqliQuery($sql);
            while ($data2 = mysqli_fetch_assoc($result2)) {
                $oldTermId = $data2['term_id'];
                if (isset($oldTags[$oldTermId])) {
                    $postTags[] = $oldTags[$oldTermId]->getId();
                }
            }

            $data['featured_img'] = '';

            $sql = 'SELECT wm2.meta_value as featured_img
                FROM
                    '.$_pref.'posts p1
                LEFT JOIN
                    '.$_pref.'postmeta wm1
                    ON (
                        wm1.post_id = p1.id
                        AND wm1.meta_value IS NOT NULL
                        AND wm1.meta_key = "_thumbnail_id"
                    )
                LEFT JOIN
                    '.$_pref.'postmeta wm2
                    ON (
                        wm1.meta_value = wm2.post_id
                        AND wm2.meta_key = "_wp_attached_file"
                        AND wm2.meta_value IS NOT NULL
                    )
                WHERE
                    p1.ID="'.$data['ID'].'"
                    AND p1.post_type="post"
                ORDER BY
                    p1.post_date DESC';

            $result2 = $this->_mysqliQuery($sql);
            if ($data2 = mysqli_fetch_assoc($result2)) {
                if ($data2['featured_img']) {
                    $data['featured_img'] = \Magefan\Blog\Model\Post::BASE_MEDIA_PATH . '/' . $data2['featured_img'];
                }
            }

            /* Find Meta Data */
            $sql = 'SELECT * FROM `'.$_pref.'postmeta` WHERE `post_id` = ' . ((int)$data['ID']);
            $metaResult = $this->_mysqliQuery($sql);
            while ($metaData = mysqli_fetch_assoc($metaResult)) {

                $metaValue = trim($metaData['meta_value']);
                if (!$metaValue) {
                    continue;
                }

                switch ($metaData['meta_key']) {
                    case 'wpcf-meta-description' :
                        $data['short_content'] = $metaValue;
                        break;
                    case '_yoast_wpseo_title' :
                        $data['meta_title'] = $metaValue;
                        break;
                    case '_yoast_wpseo_metadesc' :
                        $data['meta_description'] = $metaValue;
                        break;
                }  
            } 

            /* Prepare post data */
            foreach (['post_title', 'post_name', 'post_content'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

            $creationTime = strtotime($data['post_date_gmt']);

            $content = $data['post_content'];
            $content = str_replace('<!--more-->', '<!-- pagebreak -->', $content);

            $content = preg_replace(
                '/src=[\'"]((http:\/\/|https:\/\/|\/\/)(.*)|(\s|"|\')|(\/[\d\w_\-\.]*))\/wp-content\/uploads(.*)((\.jpg|\.jpeg|\.gif|\.png|\.tiff|\.tif|\.svg)|(\s|"|\'))[\'"\s]/Ui',
                'src="$4{{media url="magefan_blog$6$8"}}$9"',
                $content
            );

            $content = $this->wordpressOutoutWrap($content);

            $wordpressPostId = $data['ID'];
            $data = [
                'store_ids' => [$this->getStoreId()],
                'title' => $data['post_title'],
                'meta_title' => isset($data['meta_title']) ? $data['meta_title'] : '',
                'meta_description' => isset($data['meta_description']) ? $data['meta_description'] : '',
                'meta_keywords' => '',
                'identifier' => $data['post_name'],
                'content_heading' => '',
                'content' => $content,
                'short_content' => isset($data['short_content']) ? $data['short_content'] : '',
                'creation_time' => $creationTime,
                'update_time' => strtotime($data['post_modified_gmt']),
                'publish_time' => $creationTime,
                'is_active' => (int)($data['post_status'] == 'publish'),
                'categories' => $postCategories,
                'tags' => $postTags,
                'featured_img' => $data['featured_img'],
            ];

            $data['identifier'] = $this->prepareIdentifier($data['identifier']);

            $post = $this->_postFactory->create();
            try {
                /* Post saving */
                $post->setData($data)->save();

                /* find post comment s*/
                $sql = 'SELECT * FROM '.$_pref.'comments WHERE `comment_approved`=1 AND `comment_post_ID` = ' . $wordpressPostId;
                $resultComments = $this->_mysqliQuery($sql);
                $commentParents = [];

                while ($comments = mysqli_fetch_assoc($resultComments)) {
                    $commentParentId = 0;
                    if (!($comments['comment_parent'] == 0) && isset($commentParents[$comments["comment_parent"]])) {
                        $commentParentId = $commentParents[$comments["comment_parent"]];
                    }
                    $commentData = [
                        'parent_id' => $commentParentId,
                        'post_id' => $post->getPostId(),
                        'status' => \Magefan\Blog\Model\Config\Source\CommentStatus::APPROVED,
                        'author_type' => \Magefan\Blog\Model\Config\Source\AuthorType::GUEST,
                        'author_nickname' => $comments['comment_author'],
                        'author_email' => $comments['comment_author_email'],
                        'text' => $comments['comment_content'],
                        'creation_time' => $comments['comment_date'],
                    ];

                    if (!$commentData['text']) {
                        continue;
                    }

                    $comment = $this->_commentFactory->create($commentData);

                    try {
                        /* Initial saving */
                        $comment->setData($commentData)->save();
                        $this->_importedCommentsCount++;
                        $commentParents[$comments["comment_ID"]] = $comment->getCommentId();
                    } catch (\Exception $e) {
                        $this->_skippedComments[] = $commentData['title'];
                        unset($comment);
                    }
                }
                $this->_importedPostsCount++;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_skippedPosts[] = $data['title'];
                $this->_logger->addDebug('Blog Post Import [' . $data['title'] . ']: '. $e->getMessage());
            }

            unset($post);
        }
        /* end */

        mysqli_close($con);
    }


    protected function wordpressOutoutWrap( $pee, $br = true )
    {
        $pre_tags = array();

        if ( trim( $pee ) === '' ) {
            return '';
        }

        // Just to make things a little easier, pad the end.
        $pee = $pee . "\n";

        /*
         * Pre tags shouldn't be touched by autop.
         * Replace pre tags with placeholders and bring them back after autop.
         */
        if ( strpos( $pee, '<pre' ) !== false ) {
            $pee_parts = explode( '</pre>', $pee );
            $last_pee  = array_pop( $pee_parts );
            $pee       = '';
            $i         = 0;

            foreach ( $pee_parts as $pee_part ) {
                $start = strpos( $pee_part, '<pre' );

                // Malformed html?
                if ( $start === false ) {
                    $pee .= $pee_part;
                    continue;
                }

                $name              = "<pre wp-pre-tag-$i></pre>";
                $pre_tags[ $name ] = substr( $pee_part, $start ) . '</pre>';

                $pee .= substr( $pee_part, 0, $start ) . $name;
                $i++;
            }

            $pee .= $last_pee;
        }
        // Change multiple <br>s into two line breaks, which will turn into paragraphs.
        $pee = preg_replace( '|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee );

        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

        // Add a double line break above block-level opening tags.
        $pee = preg_replace( '!(<' . $allblocks . '[\s/>])!', "\n\n$1", $pee );

        // Add a double line break below block-level closing tags.
        $pee = preg_replace( '!(</' . $allblocks . '>)!', "$1\n\n", $pee );

        // Standardize newline characters to "\n".
        $pee = str_replace( array( "\r\n", "\r" ), "\n", $pee );

        // Collapse line breaks before and after <option> elements so they don't get autop'd.
        if ( strpos( $pee, '<option' ) !== false ) {
            $pee = preg_replace( '|\s*<option|', '<option', $pee );
            $pee = preg_replace( '|</option>\s*|', '</option>', $pee );
        }

        /*
         * Collapse line breaks inside <object> elements, before <param> and <embed> elements
         * so they don't get autop'd.
         */
        if ( strpos( $pee, '</object>' ) !== false ) {
            $pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
            $pee = preg_replace( '|\s*</object>|', '</object>', $pee );
            $pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
        }

        /*
         * Collapse line breaks inside <audio> and <video> elements,
         * before and after <source> and <track> elements.
         */
        if ( strpos( $pee, '<source' ) !== false || strpos( $pee, '<track' ) !== false ) {
            $pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
            $pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
            $pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
        }

        // Collapse line breaks before and after <figcaption> elements.
        if ( strpos( $pee, '<figcaption' ) !== false ) {
            $pee = preg_replace( '|\s*(<figcaption[^>]*>)|', '$1', $pee );
            $pee = preg_replace( '|</figcaption>\s*|', '</figcaption>', $pee );
        }

        // Remove more than two contiguous line breaks.
        $pee = preg_replace( "/\n\n+/", "\n\n", $pee );

        // Split up the contents into an array of strings, separated by double line breaks.
        $pees = preg_split( '/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY );

        // Reset $pee prior to rebuilding.
        $pee = '';

        // Rebuild the content as a string, wrapping every bit with a <p>.
        foreach ( $pees as $tinkle ) {
            $pee .= '<p>' . trim( $tinkle, "\n" ) . "</p>\n";
        }

        // Under certain strange conditions it could create a P of entirely whitespace.
        $pee = preg_replace( '|<p>\s*</p>|', '', $pee );

        // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
        $pee = preg_replace( '!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $pee );

        // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
        $pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $pee );

        // In some cases <li> may get wrapped in <p>, fix them.
        $pee = preg_replace( '|<p>(<li.+?)</p>|', '$1', $pee );

        // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
        $pee = preg_replace( '|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $pee );
        $pee = str_replace( '</blockquote></p>', '</p></blockquote>', $pee );

        // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
        $pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)!', '$1', $pee );

        // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
        $pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $pee );

        // Optionally insert line breaks.
        if ( $br ) {
            // Replace newlines that shouldn't be touched with a placeholder.
            $pee = @preg_replace_callback( '/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee );

            // Normalize <br>
            $pee = str_replace( array( '<br>', '<br/>' ), '<br />', $pee );

            // Replace any new line characters that aren't preceded by a <br /> with a <br />.
            $pee = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $pee );

            // Replace newline placeholders with newlines.
            $pee = str_replace( '<WPPreserveNewline />', "\n", $pee );
        }

        // If a <br /> tag is after an opening or closing block tag, remove it.
        $pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*<br />!', '$1', $pee );

        // If a <br /> tag is before a subset of opening or closing block tags, remove it.
        $pee = preg_replace( '!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee );
        $pee = preg_replace( "|\n</p>$|", '</p>', $pee );

        // Replace placeholder <pre> tags with their original content.
        if ( ! empty( $pre_tags ) ) {
            $pee = str_replace( array_keys( $pre_tags ), array_values( $pre_tags ), $pee );
        }

        // Restore newlines in all elements.
        if ( false !== strpos( $pee, '<!-- wpnl -->' ) ) {
            $pee = str_replace( array( ' <!-- wpnl --> ', '<!-- wpnl -->' ), "\n", $pee );
        }

        return $pee;
    }
}
