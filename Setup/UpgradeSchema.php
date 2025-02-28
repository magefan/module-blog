<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Blog schema update
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $setup->startSetup();

        $version = $context->getVersion();
        $connection = $setup->getConnection();

        if (version_compare($version, '2.0.1') < 0) {
            foreach (['magefan_blog_post_relatedpost', 'magefan_blog_post_relatedproduct'] as $tableName) {
                // Get module table
                $tableName = $setup->getTable($tableName);

                // Check if the table already exists
                if ($connection->isTableExists($tableName) == true) {
                    $columns = [
                        'position' => [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'nullable' => false,
                            'comment' => 'Position',
                        ],
                    ];

                    foreach ($columns as $name => $definition) {
                        $connection->addColumn($tableName, $name, $definition);
                    }
                }
            }

            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'featured_img',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Thumbnail Image',
                ]
            );
        }

        if (version_compare($version, '2.2.0') < 0) {
            /* Add author field to posts table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'author_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Author ID',

                ]
            );

            $connection->addIndex(
                $setup->getTable('magefan_blog_post'),
                $setup->getIdxName($setup->getTable('magefan_blog_post'), ['author_id']),
                ['author_id']
            );
        }

        if (version_compare($version, '2.2.5') < 0) {
            /* Add layout field to posts and category table */
            foreach (['magefan_blog_post', 'magefan_blog_category'] as $table) {
                $table = $setup->getTable($table);
                $connection->addColumn(
                    $setup->getTable($table),
                    'page_layout',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Post Layout',
                    ]
                );

                $connection->addColumn(
                    $setup->getTable($table),
                    'layout_update_xml',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '64k',
                        'nullable' => true,
                        'comment' => 'Post Layout Update Content',
                    ]
                );

                $connection->addColumn(
                    $setup->getTable($table),
                    'custom_theme',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 100,
                        'nullable' => true,
                        'comment' => 'Post Custom Theme',
                    ]
                );

                $connection->addColumn(
                    $setup->getTable($table),
                    'custom_layout',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Post Custom Template',
                    ]
                );

                $connection->addColumn(
                    $setup->getTable($table),
                    'custom_layout_update_xml',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '64k',
                        'nullable' => true,
                        'comment' => 'Post Custom Layout Update Content',
                    ]
                );

                $connection->addColumn(
                    $setup->getTable($table),
                    'custom_theme_from',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        'nullable' => true,
                        'comment' => 'Post Custom Theme Active From Date',
                    ]
                );

                $connection->addColumn(
                    $setup->getTable($table),
                    'custom_theme_to',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        'nullable' => true,
                        'comment' => 'Post Custom Theme Active To Date',
                    ]
                );
            }
        }

        if (version_compare($version, '2.3.0') < 0) {
            /* Add meta title field to posts table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'meta_title',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Post Meta Title',
                    'after' => 'title'
                ]
            );

            /* Add og tags fields to post table */
            foreach (['type', 'img', 'description', 'title'] as $type) {
                $connection->addColumn(
                    $setup->getTable('magefan_blog_post'),
                    'og_' . $type,
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Post OG ' . ucfirst($type),
                        'after' => 'identifier'
                    ]
                );
            }

            /* Add meta title field to category table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_category'),
                'meta_title',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Category Meta Title',
                    'after' => 'title'
                ]
            );

            /**
             * Create table 'magefan_blog_tag'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('magefan_blog_tag')
            )->addColumn(
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Tag ID'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Tag Title'
            )->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => true, 'default' => null],
                'Tag String Identifier'
            )->addIndex(
                $setup->getIdxName('magefan_blog_tag', ['identifier']),
                ['identifier']
            )->setComment(
                'Magefan Blog Tag Table'
            );
            $setup->getConnection()->createTable($table);

            /**
             * Create table 'magefan_blog_post_tag'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('magefan_blog_post_tag')
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'primary' => true],
                'Post ID'
            )->addColumn(
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'primary' => true],
                'Tag ID'
            )->addIndex(
                $setup->getIdxName('magefan_blog_post_tag', ['tag_id']),
                ['tag_id']
            )->addForeignKey(
                $setup->getFkName('magefan_blog_post_tag', 'post_id', 'magefan_blog_post', 'post_id'),
                'post_id',
                $setup->getTable('magefan_blog_post'),
                'post_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName('magefan_blog_post_tag', 'tag_id', 'magefan_blog_tag', 'tag_id'),
                'tag_id',
                $setup->getTable('magefan_blog_tag'),
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Magefan Blog Post To Category Linkage Table'
            );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($version, '2.4.4') < 0) {
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'media_gallery',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '2M',
                    'nullable' => true,
                    'comment' => 'Media Gallery',
                ]
            );
        }

        if (version_compare($version, '2.5.2') < 0) {
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'secret',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '32',
                    'nullable' => true,
                    'comment' => 'Post Secret',
                ]
            );

            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'views_count',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    'nullable' => true,
                    'comment' => 'Post Views Count',
                ]
            );

            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'is_recent_posts_skip',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    'nullable' => true,
                    'comment' => 'Is Post Skipped From Recent Posts',
                ]
            );

            $connection->addIndex(
                $setup->getTable('magefan_blog_post'),
                $setup->getIdxName($setup->getTable('magefan_blog_post'), ['views_count']),
                ['views_count']
            );

            $connection->addIndex(
                $setup->getTable('magefan_blog_post'),
                $setup->getIdxName($setup->getTable('magefan_blog_post'), ['is_recent_posts_skip']),
                ['is_recent_posts_skip']
            );
        }

        if (version_compare($version, '2.5.3') < 0) {
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'short_content',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '2M',
                    'nullable' => true,
                    'comment' => 'Post Short Content',
                ]
            );
        }

        if (version_compare($version, '2.6.0') < 0) {
            /**
             * Create table 'magefan_blog_comment'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('magefan_blog_comment')
            )->addColumn(
                'comment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Comment ID'
            )->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Parent Comment ID'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Post ID'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Customer ID'
            )->addColumn(
                'admin_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Admin User ID'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Comment status'
            )->addColumn(
                'author_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Author Type'
            )->addColumn(
                'author_nickname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Comment Author Nickname'
            )->addColumn(
                'author_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Comment Author Email'
            )->addColumn(
                'text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Text'
            )->addColumn(
                'creation_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Comment Creation Time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Comment Update Time'
            )->addIndex(
                $installer->getIdxName('magefan_blog_comment', ['parent_id']),
                ['parent_id']
            )->addIndex(
                $installer->getIdxName('magefan_blog_comment', ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName('magefan_blog_comment', ['customer_id']),
                ['customer_id']
            )->addIndex(
                $installer->getIdxName('magefan_blog_comment', ['admin_id']),
                ['admin_id']
            )->addIndex(
                $installer->getIdxName('magefan_blog_comment', ['status']),
                ['status']
            )->addForeignKey(
                $installer->getFkName('magefan_blog_comment', 'post_id', 'magefan_blog_post', 'post_id'),
                'post_id',
                $installer->getTable('magefan_blog_post'),
                'post_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($version, '2.6.2') < 0) {
            /* Add include in menu field into categories table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_category'),
                'include_in_menu',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'comment' => 'Category In Menu',
                    'after' => 'position'
                ]
            );

            $connection->addIndex(
                $setup->getTable('magefan_blog_category'),
                $setup->getIdxName($setup->getTable('magefan_blog_category'), ['include_in_menu']),
                ['include_in_menu']
            );
        }

        if (version_compare($version, '2.6.3') < 0) {
            /* Add display mode field into category table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_category'),
                'display_mode',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Display Mode',
                    'after' => 'is_active'
                ]
            );
        }

        if (version_compare($version, '2.6.3.1') < 0) {
            /* Add include in recent posts into post table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'include_in_recent',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    'nullable' => false,
                    'default' => '1',
                    'comment' => 'Include in Recent Posts',
                    'after' => 'is_active'
                ]
            );
        }

        if (version_compare($version, '2.7.2') < 0) {
            /* Add position column into post table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'position',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Position',
                    'after' => 'include_in_recent'
                ]
            );

            $connection->addColumn(
                $setup->getTable('magefan_blog_category'),
                'posts_sort_by',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Post Sort By',
                    'after' => 'position'
                ]
            );
        }

        if (version_compare($version, '2.8.0') < 0) {
            /* Add layout field to tag table */
            $table = $setup->getTable('magefan_blog_tag');
            $connection->addColumn(
                $setup->getTable($table),
                'page_layout',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Tag Layout',
                ]
            );
            $connection->addColumn(
                $setup->getTable($table),
                'is_active',
                [
                    'type' =>\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 1,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Is Tag Active'
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'content',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '2M',
                    [],
                    'comment' =>'Tag Content'
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'meta_title',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Tag Meta Title',
                    'after' => 'title'
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'meta_keywords',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Tag Meta Keywords',
                    'after' => 'title'
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'meta_description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Tag Meta Description',
                    'after' => 'title'
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'layout_update_xml',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Tag Layout Update Content',
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'custom_theme',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'comment' => 'Tag Custom Theme',
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'custom_layout',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Tag Custom Template',
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'custom_layout_update_xml',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Tag Custom Layout Update Content',
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'custom_theme_from',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Tag Custom Theme Active From Date',
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'custom_theme_to',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Tag Custom Theme Active To Date',
                ]
            );

            $connection->addIndex(
                $setup->getTable($table),
                $setup->getIdxName($setup->getTable($table), ['is_active']),
                ['is_active']
            );
        }

        /* remove multi-fulltext, it does not supported in some DB
        if (version_compare($version, '2.8.3.1') < 0) {
            // Fix issue https://github.com/magefan/module-blog/issues/205
            $table = $setup->getTable('magefan_blog_post');
            foreach (['title', 'content', 'short_content'] as $field) {
                $connection->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [$field],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    [$field],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }
        */

        if (version_compare($version, '2.8.4.1') < 0) {
            $table = $setup->getTable('magefan_blog_tag');
            $connection->addColumn(
                $setup->getTable($table),
                'meta_robots',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Tag Default Robots',
                    'after' => 'title'
                ]
            );
        }

        if (version_compare($version, '2.9.1') < 0) {
            $table = $setup->getTable('magefan_blog_post');
            $connection->addColumn(
                $setup->getTable($table),
                'featured_img_alt',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Featured Image Alt',
                    'after' => 'featured_img'
                ]
            );

            $connection->addColumn(
                $setup->getTable($table),
                'comments_count',
                [
                    'type' =>\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Post Comment Counts'

                ]
            );
        }

        if (version_compare($version, '2.9.8') < 0) {
            /**
             * Create table 'magefan_blog_tag_store'
             */
            $table = $connection->newTable(
                $setup->getTable('magefan_blog_tag_store')
            )->addColumn(
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'primary' => true],
                'Tag ID'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addIndex(
                $setup->getIdxName('magefan_blog_tag_store', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $setup->getFkName('magefan_blog_tag_store', 'tag_id', 'magefan_blog_tag', 'tag_id'),
                'tag_id',
                $setup->getTable('magefan_blog_tag'),
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName('magefan_blog_tag_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Magefan Blog Tag To Store Linkage Table'
            );
            $connection->createTable($table);
        }

        if (version_compare($version, '2.10.0') < 0) {
            foreach (['magefan_blog_tag', 'magefan_blog_category'] as $table) {
                $connection->addColumn(
                    $setup->getTable($table),
                    'posts_per_page',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Posts Per Page',

                    ]
                );
                $connection->addColumn(
                    $setup->getTable($table),
                    'posts_list_template',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 100,
                        'nullable' => true,
                        'comment' => 'Posts List Template',
                    ]
                );
            }
        }

        if (version_compare($version, '2.11.1') < 0) {
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'structure_data_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Rich Snippet / Structured Data',
                    'after' => 'meta_description'
                ]
            );

            /* Add reading time to posts table */
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'reading_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'Post Reading Time',
                    'after' => 'views_count'
                ]
            );
        }

        if (version_compare($version, '2.11.3') < 0) {
            if ($connection->isTableExists($setup->getTable('magefan_blog_category'))) {
                $connection->addIndex(
                    $setup->getTable('magefan_blog_category'),
                    $setup->getIdxName(
                        $setup->getTable('magefan_blog_category'),
                        ['is_active']
                    ),
                    ['is_active']
                );
            }

            if ($connection->isTableExists($setup->getTable('magefan_blog_post'))) {
                $connection->addIndex(
                    $setup->getTable('magefan_blog_post'),
                    $setup->getIdxName(
                        $setup->getTable('magefan_blog_post'),
                        ['is_active']
                    ),
                    ['is_active']
                );

                $connection->addIndex(
                    $setup->getTable('magefan_blog_post'),
                    $setup->getIdxName(
                        $setup->getTable('magefan_blog_post'),
                        ['include_in_recent']
                    ),
                    ['include_in_recent']
                );

                $connection->addIndex(
                    $setup->getTable('magefan_blog_post'),
                    $setup->getIdxName(
                        $setup->getTable('magefan_blog_post'),
                        ['publish_time']
                    ),
                    ['publish_time']
                );
            }
        }

        if (version_compare($version, '2.12.1') < 0) {
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'meta_robots',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Default Robots',
                    'after' => 'meta_description'
                ]
            );
            $connection->addColumn(
                $setup->getTable('magefan_blog_category'),
                'meta_robots',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Default Robots',
                    'after' => 'meta_description'
                ]
            );

            $connection->addColumn(
                $setup->getTable('magefan_blog_category'),
                'include_in_sidebar_tree',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '1',
                    'comment' => 'Category In Sidebar Tree',
                    'after' => 'include_in_menu'
                ]
            );

            $connection->addIndex(
                $setup->getTable('magefan_blog_category'),
                $setup->getIdxName(
                    'magefan_blog_category',
                    ['include_in_sidebar_tree'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['include_in_sidebar_tree'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        }
        if (version_compare($version, '2.12.3') < 0) {
            $connection->addColumn(
                $setup->getTable('magefan_blog_post'),
                'custom_css',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Custom CSS',
                    'after' => 'layout_update_xml'
                ]
            );
        }
        $setup->endSetup();
    }
}
