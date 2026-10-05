<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml;

use Magento\Backend\Block\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Driver\File;

/** Warning about files of previous Blog Plus, Extra and Author versions left after options moved to Blog. */
class ObsoleteFiles extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * Files removed since previous version (2.16.2)
     */
    private const REMOVED_FILES = [
        'Magefan_Blog' => [
            'Block/Adminhtml/Category.php',
            'Block/Adminhtml/Comment.php',
            'Block/Adminhtml/Post.php',
            'Block/Adminhtml/Tag.php',
            'view/adminhtml/layout/blog_category_grid.xml',
            'view/adminhtml/layout/blog_category_grid_block.xml',
            'view/adminhtml/layout/blog_comment_grid.xml',
            'view/adminhtml/layout/blog_comment_grid_block.xml',
            'view/adminhtml/layout/blog_post_grid.xml',
            'view/adminhtml/layout/blog_post_grid_block.xml',
            'view/adminhtml/layout/blog_tag_grid.xml',
            'view/adminhtml/layout/blog_tag_grid_block.xml',
        ],
        'Magefan_BlogPlus' => [
            'Block/Adminhtml/Post/RelatedProductsRule.php',
            'Model/Config/Source/CustomerGroups.php',
            'view/adminhtml/layout/blog_category_grid_block.xml',
            'view/adminhtml/layout/blog_tag_grid_block.xml',
            'view/adminhtml/ui_component/blog_category_form.xml',
            'view/adminhtml/ui_component/blog_tag_form.xml',
            'view/adminhtml/web/js/components/url-key-handle-changes.js',
        ],
        'Magefan_BlogAuthor' => [
            'Model/Config/Source/Authors.php',
            'view/adminhtml/layout/blogauthor_author_grid.xml',
            'view/adminhtml/layout/blogauthor_author_grid_block.xml',
            'view/adminhtml/ui_component/blog_post_form.xml',
        ],
        'Magefan_BlogImport' => [
            'view/adminhtml/layout/blog_post_export_grid_block.xml',
            'view/adminhtml/layout/blog_post_grid_block.xml',
        ],
    ];

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param File $file
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        File $file,
        DirectoryList $directoryList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->componentRegistrar = $componentRegistrar;
        $this->file = $file;
        $this->directoryList = $directoryList;
    }

    /**
     * Render warning, nothing if all modules are up to date
     *
     * @return string
     */
    protected function _toHtml()
    {
        $files = $this->getFiles();
        if (!$files) {
            return '';
        }

        $commands = [];
        foreach ($files as $file) {
            $commands[] = 'rm ' . $file;
        }
        $commands[] = 'php bin/magento cache:flush';

        $text = implode(' ', [
            __('Some obsolete files are left over from previous versions after an extension upgrade.'),
            __('These files are no longer used in the latest version of the Blog extension.'),
            __('Please remove them. Run the commands:'),
        ]);

        return '<div class="messages"><div class="message message-warning warning"><div>'
            . $this->_escaper->escapeHtml($text)
            . '</div><pre>' . $this->_escaper->escapeHtml(implode("\n", $commands)) . '</pre></div></div>';
    }

    /**
     * Get existing removed files, paths are relative to Magento root
     *
     * @return string[]
     */
    private function getFiles(): array
    {
        $result = [];
        foreach (self::REMOVED_FILES as $moduleName => $files) {
            foreach ($files as $file) {
                $path = $this->getPath($moduleName, $file);
                if ($path && $this->file->isExists($path)) {
                    $result[] = $this->getRelativePath($path);
                }
            }
        }

        return $result;
    }

    /**
     * Get absolute file path inside module, null if module is not in app/code
     *
     * @param string $moduleName
     * @param string $file
     * @return string|null
     */
    private function getPath(string $moduleName, string $file): ?string
    {
        $modulePath = (string)$this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        /* Composer removes deleted files itself, only modules copied to app/code keep them */
        $appCodePath = rtrim($this->directoryList->getPath(DirectoryList::APP), '/') . '/code/';

        return strpos($modulePath, $appCodePath) === 0 ? $modulePath . '/' . $file : null;
    }

    /**
     * Get path relative to Magento root
     *
     * @param string $path
     * @return string
     */
    private function getRelativePath(string $path): string
    {
        $root = rtrim($this->directoryList->getRoot(), '/') . '/';

        return strpos($path, $root) === 0 ? substr($path, strlen($root)) : $path;
    }
}
