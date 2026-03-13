<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Plugin\Magento\Framework\View\Element\Frontend;

use Magefan\Blog\Model\Config;
use Magefan\Blog\Model\Config\Source\DesignVersion;
use Magefan\Blog\Model\TemplatePool;
use Magefan\Community\Api\HyvaThemeDetectionInterface;
// phpcs:ignoreFile Generic.Files.LineLength
class TemplatePlugin
{
    private const OLD_TO_NEW_TEMPLATES_MAP = [
        'Magefan_Blog::post/view-modern.phtml' => 'Magefan_Blog::design_modern/post/view.phtml',
        'Magefan_Blog::post/list-modern.phtml' => 'Magefan_Blog::design_modern/post/list.phtml',
        'Magefan_Blog::post/view/nextprev-modern.phtml' => 'Magefan_Blog::design_modern/post/view/nextprev.phtml',
        'Magefan_BlogExtra::post/view/categoryposts-modern.phtml' => 'Magefan_BlogExtra::design_modern/post/view/categoryposts.phtml',
        'Magefan_BlogExtra::post/view/relatedposts-modern.phtml' => 'Magefan_BlogExtra::design_modern/post/view/relatedposts.phtml',
        'Magefan_BlogExtra::sidebar/recent-modern.phtml' => 'Magefan_BlogExtra::sidebar/recent-grid.phtml',
        // !!!! 'Magefan_BlogAuthor::post/view/latest-posts-by-author-modern.phtml' => 'Magefan_BlogAuthor::design_modern/post/view/latest-posts-by-author.phtml',

        // whole list at - \Magefan\BlogExtra\Setup\Patch\Data\TemplateNamesChangeIn2025::BLOG_POST_LIST_TEMPLATE_MIGRATION
       'Magefan_BlogExtra::post/list/block-0-1-full.phtml' =>  'Magefan_BlogExtra::post/list/block-5-4.phtml',
       'Magefan_BlogExtra::post/list/block-0-2-full.phtml' =>  'Magefan_BlogExtra::post/list/block-5-3.phtml',
       'Magefan_BlogExtra::post/list/block-0-4-row-full.phtml' =>  'Magefan_BlogExtra::post/list/block-5-2.phtml',
       'Magefan_BlogExtra::post/list/block-0-5-full.phtml' =>  'Magefan_BlogExtra::post/list/block-5-3.phtml',
       'Magefan_BlogExtra::post/list/block-0-6-full.phtml' =>  'Magefan_BlogExtra::post/list/block-5-3.phtml',
       'Magefan_BlogExtra::post/list/block-1-1-full.phtml' =>  'Magefan_BlogExtra::post/list/block-1-1.phtml',
       'Magefan_BlogExtra::post/list/block-1-2-full.phtml' =>  'Magefan_BlogExtra::post/list/block-1-2.phtml',
       'Magefan_BlogExtra::post/list/block-1-3-full.phtml' =>  'Magefan_BlogExtra::post/list/block-1-3.phtml',
       'Magefan_BlogExtra::post/list/block-2-1-full.phtml' =>  'Magefan_BlogExtra::post/list/block-2-4.phtml',
       'Magefan_BlogExtra::post/list/block-2-2-full.phtml' =>  'Magefan_BlogExtra::post/list/block-2-5.phtml',
       'Magefan_BlogExtra::post/list/block-2-3.phtml' =>  'Magefan_BlogExtra::post/list/block-2-6.phtml',
       'Magefan_BlogExtra::post/list/block-2-1-shortcontent.phtml' =>  'Magefan_BlogExtra::post/list/block-2-1.phtml',
       'Magefan_BlogExtra::post/list/block-2-2-shortcontent.phtml' =>  'Magefan_BlogExtra::post/list/block-2-2.phtml',
       'Magefan_BlogExtra::post/list/block-2-3-shortcontent.phtml' =>  'Magefan_BlogExtra::post/list/block-2-3.phtml',
       'Magefan_BlogExtra::post/list/block-3-1-full.phtml' =>  'Magefan_BlogExtra::post/list/block-3-1.phtml',
       'Magefan_BlogExtra::post/list/block-3-2.phtml' =>  'Magefan_BlogExtra::post/list/block-3-2.phtml',
       'Magefan_BlogExtra::post/list/block-3-3.phtml' =>  'Magefan_BlogExtra::post/list/block-3-3.phtml',
       'Magefan_BlogExtra::post/list/block-3-4-full.phtml' =>  'Magefan_BlogExtra::post/list/block-3-4.phtml',
       'Magefan_BlogExtra::post/list/block-3-5-full.phtml' =>  'Magefan_BlogExtra::post/list/block-3-5.phtml',
       'Magefan_BlogExtra::post/list/block-3-6.phtml' =>  'Magefan_BlogExtra::post/list/block-3-6.phtml',
       'Magefan_BlogExtra::post/list/block-4-1.phtml' =>  'Magefan_BlogExtra::post/list/block-5-1.phtml',
       'Magefan_BlogExtra::post/list/block-4-2-full.phtml' =>  'Magefan_BlogExtra::post/list/block-5-1.phtml',
       'Magefan_BlogExtra::post/list/block-5-1-full.phtml' =>  'Magefan_BlogExtra::post/list/block-3-4.phtml',
       'Magefan_BlogExtra::post/list/block-5-2.phtml' =>  'Magefan_BlogExtra::post/list/block-3-5.phtml',
       'Magefan_BlogExtra::post/list/block-5-3.phtml' =>  'Magefan_BlogExtra::post/list/block-3-4.phtml'
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HyvaThemeDetectionInterface
     */
    private $hyvaThemeDetection;

    /**
     * @var TemplatePool
     */
    private $templatePool;

    /**
     * @param Config $config
     * @param HyvaThemeDetectionInterface $hyvaThemeDetection
     * @param TemplatePool $templatePool
     */
    public function __construct(
        Config $config,
        HyvaThemeDetectionInterface $hyvaThemeDetection,
        TemplatePool $templatePool
    ) {
        $this->config = $config;
        $this->hyvaThemeDetection = $hyvaThemeDetection;
        $this->templatePool = $templatePool;
    }

    /**
     * Modify the template path after it is retrieved, based on design version and configuration.
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param string $result
     * @return array|mixed|string|string[]
     */
    public function afterGetTemplate(\Magento\Framework\View\Element\Template $subject, $result)
    {
        $template = $result;

        if (!$template || !$this->isBlogTemplate($template) || !$this->config->isEnabled()) {
            return $template;
        }

        $designVersion = $this->config->getDesignVersion();

        // If template is already using the design folder (e.g., ::design_modern/...)
        if (false !== strpos($template, $designVersion . '/')) {
            // Check if there is custom overrides for that template in current theme
            $overriddenTemplate = $this->getOldOverriddenTemplate($subject, $template);
            return $overriddenTemplate ?: $template;
        }

        // Fix bug where some blog templates are overridden in theme
        // and already contain the suffix "-modern" in the file name
        if ($designVersion === DesignVersion::MODERN) {
            $modernTemplate = str_replace('.phtml', '-modern.phtml', $template);

            if (isset(self::OLD_TO_NEW_TEMPLATES_MAP[$modernTemplate])) {
                $template = $modernTemplate;
            }
        }

        // for cases when template set in cms block/page via widget
        // and there is no custom overrides for that template in current theme
        if (isset(self::OLD_TO_NEW_TEMPLATES_MAP[$template])) {
            $newDesignTemplate = self::OLD_TO_NEW_TEMPLATES_MAP[$template];
            if (!in_array($designVersion, [DesignVersion::INITIAL, DesignVersion::MODERN])) {
                /* e.g. valor desing */
                $tpis = $this->templatePool->getAll('blog_post_list');
                foreach ($tpis as $item) {
                    if ($item['template'] == $newDesignTemplate && !empty($item['template_' . $designVersion])) {
                        $newDesignTemplate = $item['template_' . $designVersion];
                        $subject->setData('template_type', $item['value']);
                        return $newDesignTemplate;
                    }
                }
            }

            if (!$subject->getTemplateFile($template)) {
                return $newDesignTemplate;
            }
        }

        $newDesignTemplate = $this->getDesignVersionTemplate($template, $designVersion);

        if ($oldTemplate = $this->getOldOverriddenTemplate($subject, $newDesignTemplate)) {
            return $oldTemplate;
        }

        if ($subject->getTemplateFile($newDesignTemplate)) {
            return $newDesignTemplate;
        }

        $newDesignTemplateBasic = str_replace(['Extra','Plus'], '', $newDesignTemplate);

        if ($subject->getTemplateFile($newDesignTemplateBasic)) {
            return $newDesignTemplateBasic;
        }

        return $template;
    }

    /**
     * Check if template is from blog module
     *
     * @param string $template
     * @return bool
     */
    private function isBlogTemplate(string $template): bool
    {
        return false !== strpos($template, 'Magefan_Blog') || false !== strpos($template, 'Hyva_MagefanBlog');
    }

    /**
     * Return path to old template - may be found only if it is overridden in theme
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param string $template
     * @return string
     */
    private function getOldOverriddenTemplate(\Magento\Framework\View\Element\Template $subject, string $template): string
    {
        $designVersion = $this->config->getDesignVersion();
        $template = str_replace($designVersion. '/', '', $template);
        $oldTemplate = array_search($template, self::OLD_TO_NEW_TEMPLATES_MAP);

        if ($oldTemplate && $subject->getTemplateFile($oldTemplate)) {
            return $oldTemplate;
        }

        return '';
    }

    /**
     * Return new template path based on design version
     *
     * @param string $template
     * @param string $designVersion
     * @return string
     */
    private function getDesignVersionTemplate(string $template, string $designVersion): string
    {
        /* from Magefan_Blog::post/view.phtml to Magefan_Blog::design_valor/post/view.phtml */
        $newDesignTemplate = str_replace('::', '::' . $designVersion . '/', $template);

        return $newDesignTemplate;
    }
}
