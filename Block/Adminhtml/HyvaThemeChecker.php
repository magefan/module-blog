<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magefan\Blog\Model\Config;
class HyvaThemeChecker extends Template
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ModuleManager $moduleManager
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ThemeProviderInterface $themeProvider
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModuleManager $moduleManager,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ThemeProviderInterface $themeProvider,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
        $this->config = $config;
    }

        /**
     * @return array
     */
    public function getWitchModuleIsInstalled(): array
    {

        $modules = [
            'Magefan_Blog' => 'https://github.com/magefan/hyva-theme-blog',
            'Magefan_BlogPlus' => 'https://github.com/magefan/hyva-theme-blog-plus',
            'Magefan_BlogExtra' => 'https://github.com/magefan/hyva-theme-blog-extra',
            'Magefan_BlogAuthor' => 'https://github.com/magefan/hyva-theme-blog-author',
            'Magefan_AutoRelatedProduc' => 'https://github.com/magefan/hyva-theme-auto-related-product',
            'Magefan_AutoRelatedProductPlus' => 'https://github.com/magefan/hyva-theme-auto-related-product-plus',
            'Magefan_AutoLanguageSwitcher' => 'https://github.com/magefan/hyva-theme-auto-language-switcher'
        ];


        $hyvaModules = [];
        foreach ($modules as $module => $url){
           if ($this->moduleManager->isEnabled($module)) {
               $hyvaModule = 'Hyva_' . str_replace('_', '', $module);
               if (!$this->moduleManager->isEnabled($hyvaModule)) {
                   $hyvaModules[$hyvaModule] = $url;
               }
           }
        }
        return $hyvaModules;
    }

    /**
     * @return bool
     */
    private function isHyvaThemeInUse(): bool
    {
        $hyvaThemeEnabled = $this->moduleManager->isEnabled('Hyva_Theme');
//        $hyvaThemeEnabled = true;
        if ($hyvaThemeEnabled) {
            $stores = $this->storeManager->getStores();

            foreach ($stores as $store) {
                $storeId = $store->getId();
                $themeId = $this->scopeConfig->getValue(
                    \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                if ($themeId) {
                    $theme = $this->themeProvider->getThemeById($themeId);
                    $theme->getThemePath();
                    $themePath = $theme->getThemePath();
                    if (false !== stripos($themePath, 'hyva')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Produce and return block's html output
     *
     * This method should not be overridden. You can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->config->isEnabled()){
            if (!$this->isHyvaThemeInUse()) {
                return '';
            }

            return parent::toHtml();
        }
    }
}
