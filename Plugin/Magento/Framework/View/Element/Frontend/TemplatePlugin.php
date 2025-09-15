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
     * @var \Magefan\Community\Api\HyvaThemeDetectionInterface
     */
    private $hyvaThemeDetection;

    /**
     * @param Template\Context $context
     * @param File $fileDriver
     * @param Config $config
     * @param \Magefan\Community\Api\HyvaThemeDetectionInterface $hyvaThemeDetection
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        File $fileDriver,
        Config $config,
        \Magefan\Community\Api\HyvaThemeDetectionInterface $hyvaThemeDetection,
        array $data = []
    )
    {
        $this->fileDriver = $fileDriver;
        $this->config = $config;
        $this->hyvaThemeDetection = $hyvaThemeDetection;
        parent::__construct($context, $data);
    }

    /**
     * @param Template $subject
     * @param $result
     * @return array|mixed|string|string[]|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function afterGetTemplate(\Magento\Framework\View\Element\Template $subject, $result)
    {

        if ($this->config->isEnabled() && $result && strpos($result, 'Magefan_Blog') !== false) {
            $designVersion = $this->config->getDesignVersion();
            if (strpos($result, $designVersion . '/') !== false) {
                return $result;
            }

            $newTemplate = str_replace('::', '::' . $designVersion . '/', $result);
            $newTemplate = str_replace('-modern', '', $newTemplate);

            //Magefan_Blog::2025-07/widget/recent.phtml
            //Magefan_Blog::2025-07/widget/recent.phtml


            if ($designVersion == DesignVersion::VALOR) {
                $valorMapping = [
                    'Magefan_Blog::' . $designVersion . '/widget/recent.phtml' => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-1-1.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-2-5.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-3-6.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-5-1.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-1-2.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-2-6.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-4-1.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-5-2.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-1-3.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-3-1.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-4-2.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-5-3.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-2-1.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-3-2.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-4-3.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-5-4.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-2-2.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-3-3.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-4-4.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-2-3.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-3-4.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-4-5.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-2-4.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-3-5.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                    'Magefan_BlogExtra::' . $designVersion . '/post/list/block-4-6.phtml'  => 'Magefan_Blog::' . $designVersion . '/post/article.phtml',
                ];

                foreach ($valorMapping as $old => $new) {
                    if ($newTemplate === $old) {
                        $newTemplate = $new;
                        break;
                    }
                }
            }

            /*
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
            */


            $template = self::deprecatedPaths[$newTemplate] ?? $newTemplate;
            $filePath = $this->getTemplateFile($template);

            if ($this->hyvaThemeDetection->execute()) {
                $modifiedPath = $result;
                if ($this->config->getDesignVersion() == DesignVersion::VALOR){
                    $modifiedPath = preg_replace('/\d{4}-(0[1-9]|1[0-2])/', $this->config->getDesignVersion(), $result);
                }
                if (strpos($modifiedPath, $this->config->getDesignVersion()) == false) {
                    $modifiedPath = str_replace('::', '::' . $this->config->getDesignVersion() . '/', $modifiedPath);
                }

                $hyvaTemplatePath = str_replace('Magefan_Blog', 'Hyva_MagefanBlog', $modifiedPath);

                $filePath = $this->getTemplateFile($hyvaTemplatePath);

                if ($filePath && $this->fileDriver->isExists($filePath)) {
                    return $hyvaTemplatePath;
                }
            }

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
            /*
            if (strpos((string) $result, 'article.phtml') !== false) {
                return $result;
            }
            */

        }

        return $result;
    }
}