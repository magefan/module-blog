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
     * Style constructor.
     * @param Source $source
     * @param AssetRepository $assetRepository
     */
    public function __construct(
        Source $source,
        AssetRepository $assetRepository
    ) {
        $this->source = $source;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @return null|string
     */
    public function getStyle($file)
    {
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

        $asset = $this->assetRepository->createAsset($file);

        $fileContent = null;
        /*
        if ($this->source->getFile($asset)) {
            $fileContent = $this->source->getContent($asset);
        }
        */

        $file = '../' . $this->source->findRelativeSourceFilePath($asset);

        if ($file != '../' && file_exists($file)) {
            $fileContent = file_get_contents($file);
        } else {
            $file = $this->source->findRelativeSourceFilePath($asset);
            if ($file && file_exists($file)) {
                $fileContent = file_get_contents($file);
            }
        }

        $fileContent = str_replace(
            'url(../',
            ' url(' . dirname($asset->getUrl('')) . '/../',
            $fileContent
        );

        if (!trim($fileContent)) {
            $fileContent = '/* ' .  $file . '.css is empty */';
        }

        return PHP_EOL . '
        <!-- Start CSS ' . $file . ' ' . ((int)(strlen($fileContent) / 1024)) . 'Kb -->
        <style>' . $fileContent . '</style>';
    }
}
