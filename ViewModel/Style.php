<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\ViewModel;

use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magefan\Blog\Model\Config;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

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
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @param Source $source
     * @param AssetRepository $assetRepository
     * @param Config $config
     * @param FileDriver $fileDriver
     */
    public function __construct(
        Source $source,
        AssetRepository $assetRepository,
        Config $config,
        FileDriver $fileDriver
    ) {
        $this->source = $source;
        $this->assetRepository = $assetRepository;
        $this->config = $config;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Get style
     *
     * @param string $file
     * @return null|string
     */
    public function getStyle($file): string
    {
        if (strpos($file, 'bootstrap-4.4.1-custom-min.css') !== false
            && !$this->config->getIncludeBootstrapCustomMini()
        ) {
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
        if (!$file || !$this->fileDriver->isExists($file)) {
            $file = $this->source->findRelativeSourceFilePath($asset);
            if ($file && !$this->fileDriver->isExists($file)) {
                $file = '../' . $file;

            }
        }

        if ($file && $this->fileDriver->isExists($file)) {
            $fileContent = $this->fileDriver->fileGetContents($file);
        }

        $fileContent = str_replace(
            'url(../',
            ' url(' . $this->fileDriver->getParentDirectory($asset->getUrl('')) . '/../',
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
