<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).

 * This script fixes the issue with the broken page builder on the blog admin pages after the Magento update
 * to use it copy it to the Magento root folder and run the command
 * php blog-page-guilder-fix.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\DB\FieldToConvert;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$obj = $bootstrap->getObjectManager();

$state = $obj->get(Magento\Framework\App\State::class);
$state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

$converters = [];
$queryModifierFactory = $obj->create(\Magento\Framework\DB\Select\QueryModifierFactory::class);
$moduleDataSetup = $obj->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
$aggregatedFieldDataConverter = $obj->create(\Magento\Framework\DB\AggregatedFieldDataConverter::class);
$entitiesPool = $obj->create(\Magento\PageBuilder\Model\UpgradableEntitiesPool::class);

const PAGE_BUILDER_CONTENT_PATTERN = '%data-content-type="%';

$pageBuilderStripStyles = $obj->create(\Magento\PageBuilder\Setup\Converters\PageBuilderStripStyles::class);

$magefanTables = [
    'magefan_blog_author' => [
        'identifier' => 'author_id',
        'fields' => [
            'content' => 'false'
        ]
    ],
    'magefan_blog_category' => [
        'identifier' => 'category_id',
        'fields' => [
            'content' => 'false'
        ]
    ],
    'magefan_blog_comment' => [
        'identifier' => 'comment_id',
        'fields' => [
            'text' => 'false'
        ]
    ],
    'magefan_blog_post' => [
        'identifier' => 'post_id',
        'fields' => [
            'content' => 'false',
            'short_content' => 'false'
        ]
    ],
    'magefan_blog_tag' => [
        'identifier' => 'tag_id',
        'fields' => [
            'content' => 'false'
        ]
    ]
];

$fields = [];
foreach ($magefanTables as $tableName => $tableInfo) {
    foreach ($tableInfo['fields'] as $fieldName => $upgradeField) {
        if (!$upgradeField) {
            continue;
        }

        $queryModifier = $queryModifierFactory->create(
            'like',
            [
                'values' => [
                    $fieldName => PAGE_BUILDER_CONTENT_PATTERN
                ]
            ]
        );

        foreach ([\Magento\PageBuilder\Setup\Converters\PageBuilderStripStyles::class] as $converter) {
            $fields[] = new FieldToConvert(
                $converter,
                $moduleDataSetup->getTable($tableName),
                $tableInfo['identifier'],
                $fieldName,
                $queryModifier
            );
        }
    }
}

$aggregatedFieldDataConverter->convert($fields, $moduleDataSetup->getConnection());
