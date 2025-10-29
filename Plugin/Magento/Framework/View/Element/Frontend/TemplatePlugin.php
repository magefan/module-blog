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
        'Magefan_BlogExtra::design_modern/sidebar/recent.phtml' => 'Magefan_BlogExtra::sidebar/recent-modern.phtml',

        // whole list at - \Magefan\BlogExtra\Setup\Patch\Data\TemplateNamesChangeIn2025::BLOG_POST_LIST_TEMPLATE_MIGRATION
        'Magefan_BlogExtra::post/list/block-5-4.phtml' => 'Magefan_BlogExtra::post/list/block-0-1-full.phtml',
        'Magefan_BlogExtra::post/list/block-5-3.phtml' => 'Magefan_BlogExtra::post/list/block-0-2-full.phtml',
        'Magefan_BlogExtra::post/list/block-5-2.phtml' => 'Magefan_BlogExtra::post/list/block-0-4-full.phtml',
        'Magefan_BlogExtra::post/list/block-5-3.phtml' => 'Magefan_BlogExtra::post/list/block-0-5-full.phtml',
        'Magefan_BlogExtra::post/list/block-5-3.phtml' => 'Magefan_BlogExtra::post/list/block-0-6-full.phtml',
        'Magefan_BlogExtra::post/list/block-1-1.phtml' => 'Magefan_BlogExtra::post/list/block-1-1-full.phtml',
        'Magefan_BlogExtra::post/list/block-1-2.phtml' => 'Magefan_BlogExtra::post/list/block-1-2-full.phtml',
        'Magefan_BlogExtra::post/list/block-1-3.phtml' => 'Magefan_BlogExtra::post/list/block-1-3-full.phtml',
        'Magefan_BlogExtra::post/list/block-2-4.phtml' => 'Magefan_BlogExtra::post/list/block-2-1-full.phtml',
        'Magefan_BlogExtra::post/list/block-2-5.phtml' => 'Magefan_BlogExtra::post/list/block-2-2-full.phtml',
        'Magefan_BlogExtra::post/list/block-2-6.phtml' => 'Magefan_BlogExtra::post/list/block-2-3.phtml',
        'Magefan_BlogExtra::post/list/block-2-1.phtml' => 'Magefan_BlogExtra::post/list/block-2-1-shortcontent.phtml',
        'Magefan_BlogExtra::post/list/block-2-2.phtml' => 'Magefan_BlogExtra::post/list/block-2-2-shortcontent.phtml',
        'Magefan_BlogExtra::post/list/block-2-3.phtml' => 'Magefan_BlogExtra::post/list/block-2-3-shortcontent.phtml',
        'Magefan_BlogExtra::post/list/block-3-1.phtml' => 'Magefan_BlogExtra::post/list/block-3-1-full.phtml',
        'Magefan_BlogExtra::post/list/block-3-2.phtml' => 'Magefan_BlogExtra::post/list/block-3-2.phtml',
        'Magefan_BlogExtra::post/list/block-3-3.phtml' => 'Magefan_BlogExtra::post/list/block-3-3.phtml',
        'Magefan_BlogExtra::post/list/block-3-4.phtml' => 'Magefan_BlogExtra::post/list/block-3-4.phtml',
        'Magefan_BlogExtra::post/list/block-3-5.phtml' => 'Magefan_BlogExtra::post/list/block-3-5.phtml',
        'Magefan_BlogExtra::post/list/block-3-6.phtml' => 'Magefan_BlogExtra::post/list/block-3-6.phtml',
        'Magefan_BlogExtra::post/list/block-5-1.phtml' => 'Magefan_BlogExtra::post/list/block-4-1.phtml',
        'Magefan_BlogExtra::post/list/block-5-1.phtml' => 'Magefan_BlogExtra::post/list/block-4-2-full.phtml',
        'Magefan_BlogExtra::post/list/block-3-4.phtml' => 'Magefan_BlogExtra::post/list/block-5-1-full.phtml',
        'Magefan_BlogExtra::post/list/block-3-5.phtml' => 'Magefan_BlogExtra::post/list/block-5-2.phtml',
        'Magefan_BlogExtra::post/list/block-3-4.phtml' => 'Magefan_BlogExtra::post/list/block-5-3.phtml'
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

        if (
            !$template
            || (false === strpos($template, 'Magefan_Blog') && false === strpos($template, 'Hyva_MagefanBlog'))
            || !$this->config->isEnabled()
        ) {
            return $template;
        }

        $designVersion = $this->config->getDesignVersion();

        /* Template is already in design folder */
        if (false !== strpos($template, $designVersion . '/')) {
            /* Check if there is custom overrides for that template in current theme */
            if ($oldTemplate = $this->getOldSelfOverriddenTemplate($subject, $template)) {
                return $oldTemplate;
            }

            return $template;
        }

        /* for cases when template set in cms block/page via widget
           and there is no custom overrides for that template in current theme */
        if (false !== ($newTemplate = array_search($template, self::OLD_PATH_TO_MODERN_TEMPLATES)) && !$subject->getTemplateFile($template)) {
            return $newTemplate;
        }

        //$baseTemplate = str_replace('-modern', '', $template);

        /* from Magefan_Blog::post/view.phtml to Magefan_Blog::design_valor/post/view.phtml */
        $newTemplate = str_replace('::', '::' . $designVersion . '/', $template);

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

        if ($oldTemplate = $this->getOldSelfOverriddenTemplate($subject, $newTemplate)) {
            return $oldTemplate;
        }

        $newTemplateBasic = str_replace(['Extra','Plus'], '', $newTemplate);

        if ($subject->getTemplateFile($newTemplate)) {
            return $newTemplate;
        } elseif ($subject->getTemplateFile($newTemplateBasic)) {
            return $newTemplateBasic;
        }

        return $template;
    }

    /**
     * @param string $template
     * @return string
     */
    private function getOldSelfOverriddenTemplate(\Magento\Framework\View\Element\Template $subject, string $template): string
    {
        $oldTemplate = self::OLD_PATH_TO_MODERN_TEMPLATES[$template] ?? '';

        if ($oldTemplate && $subject->getTemplateFile($oldTemplate)) {
            return $oldTemplate;
        }

        return '';
    }
}
