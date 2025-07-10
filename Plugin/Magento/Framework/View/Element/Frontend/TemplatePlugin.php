<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Plugin\Magento\Framework\View\Element\Frontend;

use Magefan\Blog\Model\Config;
use Magefan\Blog\Model\Config\Source\DesignVersion;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Element\Template;

class TemplatePlugin extends \Magento\Framework\View\Element\Template
{

    const deprecatedPaths = [
        'Magefan_Blog::2021-06/post/view.phtml' => 'Magefan_Blog::post/view-modern.phtml',
        'Magefan_Blog::2021-06/post/view/nextprev.phtml' => 'Magefan_Blog::post/view/nextprev-modern.phtml',
        'Magefan_Blog::2015-11/post/view.phtml' => 'Magefan_Blog::post/view.phtml',
        'Magefan_Blog::2015-11/post/view/nextprev.phtml' => 'Magefan_Blog::post/view/nextprev.phtml',
        'Magefan_BlogAuthor::2021-06/post/view/latest-posts-by-author.phtml' => 'Magefan_BlogAuthor::post/view/latest-posts-by-author-modern.phtml',
        'Magefan_BlogAuthor::2015-11/post/view/latest-posts-by-author.phtml' => 'Magefan_BlogAuthor::post/view/latest-posts-by-author.phtml',
        'Magefan_BlogExtra::2021-06/post/view/categoryposts.phtml' => 'Magefan_BlogExtra::post/view/categoryposts-modern.phtml',
        'Magefan_BlogExtra::2015-11/post/view/categoryposts.phtml' => 'Magefan_BlogExtra::post/view/categoryposts.phtml',
        'Magefan_BlogExtra::2021-06/post/view/relatedposts.phtml' => 'Magefan_BlogExtra::post/view/relatedposts-modern.phtml',
        'Magefan_Blog::2015-11/post/view/relatedposts.phtml' => 'Magefan_Blog::post/view/relatedposts.phtml',
        'Magefan_BlogExtra::2021-06/sidebar/recent.phtml' => 'Magefan_BlogExtra::sidebar/recent-modern.phtml',
        'Magefan_Blog::2015-11/sidebar/recent.phtml' => 'Magefan_Blog::sidebar/recent.phtml',
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Template\Context $context
     * @param File $fileDriver
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        File $fileDriver,
        Config $config,
        array $data = []
    )
    {
        $this->fileDriver = $fileDriver;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param Template $subject
     * @param $result
     * @return array|string|string[]|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function afterGetTemplate(\Magento\Framework\View\Element\Template $subject, $result)
    {
        if ($result && strpos($result, 'Magefan_Blog') !== false) {
            if (strpos($result, $this->config->getDesignVersion() . '/') !== false) {
                return $result;
            }

            $newTemplate = str_replace('::', '::' . $this->config->getDesignVersion() . '/', $result);
            $newTemplate = str_replace('-modern', '', $newTemplate);
            if ($this->config->getDesignVersion() == DesignVersion::VALOR
                && strpos($newTemplate, 'widget') !== false
                && strpos($newTemplate, 'recent-post-slider') == false
                && strpos($newTemplate, 'authors-list') == false
            ) {
                $newTemplate = preg_replace(
                    '#(widget/)[^/]+(\.phtml)$#',
                    '${1}blog-widget${2}',
                    $newTemplate
                );
            }

            /*if (isset(self::deprecatedPaths[$newTemplate])) {
                $filePath = $this->getTemplateFile(self::deprecatedPaths[$newTemplate]);
                if ($filePath && $this->fileDriver->isExists($filePath)) {
                    $result = self::deprecatedPaths[$newTemplate];
                } else {
                    $filePath = $this->getTemplateFile($newTemplate);
                    if ($filePath && $this->fileDriver->isExists($filePath)) {
                        $result = $newTemplate;
                    }
                }
            } else {
                $filePath = $this->getTemplateFile($newTemplate);
                if ($filePath && $this->fileDriver->isExists($filePath)) {
                    $result = $newTemplate;
                }
            }*/

            $template = self::deprecatedPaths[$newTemplate] ?? $newTemplate;
            $filePath = $this->getTemplateFile($template);

            if ($filePath && $this->fileDriver->isExists($filePath)) {
                $result = $template;
            } else {
                $filePath = $this->getTemplateFile($newTemplate);
                if ($filePath && $this->fileDriver->isExists($filePath)) {
                    $result = $newTemplate;
                }
            }

            $filePath = $this->getTemplateFile(str_replace(['Extra','Plus'], '', $newTemplate));
            if ($filePath && $this->fileDriver->isExists($filePath)) {
                $result = str_replace(['Extra','Plus'], '', $newTemplate);
            }
            if (strpos((string) $result, 'article.phtml') !== false) {
                return $result;
            }
        }

        return $result;
    }
}