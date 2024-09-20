<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\ViewModel;

use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magefan\Blog\Model\Config;

/**
 * Class AbstractCss
 */
class Style implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var array
     */
    private $done = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Source $source
     * @param AssetRepository $assetRepository
     * @param Config $config
     */
    public function __construct(
        Source $source,
        AssetRepository $assetRepository,
        Config $config
    ) {
        $this->source = $source;
        $this->assetRepository = $assetRepository;
        $this->config = $config;
    }

    /**
     * @return null|string
     */
    public function getStyle($file)
    {
        if (strpos($file, 'bootstrap-4.4.1-custom-min.css') !== false && !$this->config->getIncludeBootstrapCustomMini()) {
            return '';
        }

        if (isset($this->done[$file])) {
            return '';
        }
        $this->done[$file] = true;

        if (false === strpos($file, '::')) {
            $file = 'Magefan_Blog::css/' . $file;
        }

        if (false === strpos($file, '.css')) {
            $file = $file . '.css';
        }

        $shortFileName = $file;

        $asset = $this->assetRepository->createAsset($file);

        $fileContent = '';

        $file = $this->source->getFile($asset);
        if (!$file || !file_exists($file)) {
            $file = $this->source->findRelativeSourceFilePath($asset);
            if ($file && !file_exists($file)) {
                $file = '../' . $file;

            }
        }

        if ($file && file_exists($file)) {
            $fileContent = file_get_contents($file);
        }

        $fileContent = str_replace(
            'url(../',
            ' url(' . dirname($asset->getUrl('')) . '/../',
            $fileContent
        );

        if (!trim($fileContent)) {
            $fileContent = '/* ' .  $shortFileName . '.css is empty */';
        }

        return PHP_EOL . '
        <!-- Start CSS ' . $shortFileName . ' ' . ((int)(strlen($fileContent) / 1024)) . 'Kb -->
        <style>' . $fileContent . '</style>';
    }
}
