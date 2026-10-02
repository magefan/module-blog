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
     * Files removed from modules after their form options were moved to Blog
     */
    private const REMOVED_FILES = [
        'Magefan_BlogPlus' => [
            'Block/Adminhtml/Post/RelatedProductsRule.php',
            'Model/Config/Source/CustomerGroups.php',
            'view/adminhtml/ui_component/blog_category_form.xml',
            'view/adminhtml/ui_component/blog_tag_form.xml',
            'view/adminhtml/web/js/components/url-key-handle-changes.js',
        ],
        'Magefan_BlogAuthor' => [
            'Model/Config/Source/Authors.php',
            'view/adminhtml/ui_component/blog_post_form.xml',
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

        $html = '<div class="messages"><div class="message message-warning warning"><div><strong>'
            . $this->_escaper->escapeHtml(__('Please delete these files left from previous versions:'))
            . '</strong> '
            . '</div><ul style="padding-left: 30px;">';
        foreach ($files as $file) {
            $html .= '<li><code>' . $this->_escaper->escapeHtml($file) . '</code></li>';
        }

        return $html . '</ul><div>'
            . $this->_escaper->escapeHtml(__('Then flush Magento cache.'))
            . '</div></div></div>';
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
                if (true||$path && $this->file->isExists($path)) {
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
