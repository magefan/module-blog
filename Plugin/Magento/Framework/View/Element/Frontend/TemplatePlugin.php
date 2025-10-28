<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Plugin\Magento\Framework\View\Element\Frontend;

use Magefan\Blog\Model\Config;
use Magefan\Blog\Model\Config\Source\DesignVersion;

class TemplatePlugin
{
    const OLD_PATH_TO_MODERN_TEMPLATES = [
        'Magefan_Blog::design_modern/post/view.phtml' => 'Magefan_Blog::post/view-modern.phtml',
        'Magefan_Blog::design_modern/post/list.phtml' => 'Magefan_Blog::post/list-modern.phtml',
        'Magefan_Blog::design_modern/post/view/nextprev.phtml' => 'Magefan_Blog::post/view/nextprev-modern.phtml',
        // !!!! 'Magefan_BlogAuthor::design_modern/post/view/latest-posts-by-author.phtml' => 'Magefan_BlogAuthor::post/view/latest-posts-by-author-modern.phtml',
        'Magefan_BlogExtra::design_modern/post/view/categoryposts.phtml' => 'Magefan_BlogExtra::post/view/categoryposts-modern.phtml',
        'Magefan_BlogExtra::design_modern/post/view/relatedposts.phtml' => 'Magefan_BlogExtra::post/view/relatedposts-modern.phtml',
        'Magefan_BlogExtra::design_modern/sidebar/recent.phtml' => 'Magefan_BlogExtra::sidebar/recent-modern.phtml'
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
     * @param Config $config
     * @param \Magefan\Community\Api\HyvaThemeDetectionInterface $hyvaThemeDetection
     */
    public function __construct(
        Config $config,
        \Magefan\Community\Api\HyvaThemeDetectionInterface $hyvaThemeDetection
    ) {
        $this->config = $config;
        $this->hyvaThemeDetection = $hyvaThemeDetection;
    }

    /**
     * @param \Magento\Framework\View\Element\Template $subject
     * @param $result
     * @return array|mixed|string|string[]
     */
    public function afterGetTemplate(\Magento\Framework\View\Element\Template $subject, $result)
    {
        $template = $result;

        if (!$template || !$this->config->isEnabled() ||
            (false === strpos($template, 'Magefan_Blog') && false === strpos($template, 'Hyva_MagefanBlog'))
        ) {
           return $template;
        }

        $designVersion = $this->config->getDesignVersion();

        if (false !== strpos($template, $designVersion . '/')) {
            /* Template is already in design folder */
            return $template;
        }

        // from Magefan_BlogExtra::post/list/block-5-3.phtml to Magefan_BlogExtra::design_valor/post/list/block-5-3.phtml
        $baseTemplate = str_replace('-modern', '', $template);
        $newTemplate = str_replace('::', '::' . $designVersion . '/', $baseTemplate);

        /*if ($this->hyvaThemeDetection->execute()) {
           $modifiedPath = $template;
           if ($designVersion == DesignVersion::VALOR) {
               $modifiedPath = preg_replace('/\d{4}-(0[1-9]|1[0-2])/', $designVersion, $template);
           }
           if (strpos($modifiedPath, $designVersion) == false) {
               $modifiedPath = str_replace('::', '::' . $designVersion . '/', $modifiedPath);
           }

           $hyvaTemplatePath = str_replace('Magefan_Blog', 'Hyva_MagefanBlog', $modifiedPath);

           if ($subject->getTemplateFile($hyvaTemplatePath)) {
               return $hyvaTemplatePath;
           }
       }*/


        $oldTemplate = self::OLD_PATH_TO_MODERN_TEMPLATES[$newTemplate] ?? '';

        if ($oldTemplate && $subject->getTemplateFile($oldTemplate)) {
            return $oldTemplate;
        }

        $newTemplate = str_replace(['Extra','Plus'], '', $newTemplate);

        if ($subject->getTemplateFile($newTemplate)) {
            return $newTemplate;
        }

        return $template;
    }
}
