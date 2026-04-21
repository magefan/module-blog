<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Factory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Blog image helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Image extends AbstractHelper
{
    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 100;
    /**
     * @var bool
     */
    protected $_keepAspectRatio = true;
    /**
     * @var bool
     */
    protected $_keepFrame = true;
    /**
     * @var bool
     */
    protected $_keepTransparency = true;
    /**
     * @var bool
     */
    protected $_constrainOnly = true;
    /**
     * @var array
     */
    protected $_backgroundColor = [255, 255, 255];

    /**
     * @var string
     */
    protected $_baseFile;
    /**
     * @var string
     */
    protected $_newFile;
    /**
     * @var Factory
     */
    protected $_imageFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var File
     */
    private $fileIo;

    /**
     * Image constructor.
     *
     * @param Context $context
     * @param Factory $imageFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param File $fileIo
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Factory $imageFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        File $fileIo
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->fileIo = $fileIo;
        parent::__construct($context);
    }

    /**
     * Initialize image helper
     *
     * @param string $baseFile
     * @return $this
     */
    public function init($baseFile)
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;
        return $this;
    }

    /**
     * Resize image
     *
     * @param string $width
     * @param null|int $height
     * @param null|bool $keepFrame
     * @return $this
     */
    public function resize(string $width, $height = null, $keepFrame = null)
    {
        if ($this->_baseFile) {
            $pathinfo = $this->fileIo->getPathInfo($this->_baseFile);
            if (isset($pathinfo) && isset($pathinfo['extension']) && $pathinfo['extension'] == 'webp') {
                $this->_newFile = $this->_baseFile;
            } else {
                $path = 'blog/cache/' . $width . 'x' . $height;
                if (null !== $keepFrame) {
                    $path .= '_' . (int)$keepFrame;
                }

                $this->_newFile = $path . '/' . $this->_baseFile;
                if (!$this->fileExists($this->_newFile)) {
                    try {
                        $this->resizeBaseFile($width, $height, $keepFrame);
                    } catch (\Exception $e) {
                        $this->_newFile = $this->_baseFile;
                    }
                    
                }
            }
        }
        return $this;
    }

    /**
     * Get image width and height
     *
     * @return array
     */
    public function getWidthAndHeigth(): array
    {
        return $this->getWidthAndHeight();
    }

    /**
     * Get image width and height
     *
     * @return array
     */
    public function getWidthAndHeight(): array
    {
        $file = $this->_newFile ?: $this->_baseFile;
        if (!$file) {
            return [];
        }

        if ($this->fileExists($file)) {
            try {
                $content = $this->_mediaDirectory->readFile($file);
                $imageSize = getimagesizefromstring($content);// phpcs:ignore Magento2.Functions.DiscouragedFunction
            } catch (\Exception $e) {
                $imageSize = false;
            }
            if ($imageSize) {
                return [
                    'width' => (int)$imageSize[0],
                    'height' => (int)$imageSize[1]
                ];
            }
        }

        return [];
    }

    /**
     * Resize base file
     *
     * @param string $width
     * @param string|null $height
     * @param bool $keepFrame
     * @return $this
     */
    protected function resizeBaseFile($width, $height, $keepFrame)
    {
        if (!$this->fileExists($this->_baseFile)) {
            $this->_baseFile = null;
            return $this;
        }

        if (null === $keepFrame) {
            $keepFrame = $this->_keepFrame;
        }

        $processor = $this->_imageFactory->create(
            $this->_mediaDirectory->getAbsolutePath($this->_baseFile)
        );
        $processor->keepAspectRatio($this->_keepAspectRatio);
        $processor->keepFrame((bool)$keepFrame);
        $processor->keepTransparency($this->_keepTransparency);
        $processor->constrainOnly($this->_constrainOnly);
        $processor->backgroundColor($this->_backgroundColor);
        $processor->quality($this->_quality);
        $processor->resize($width, $height);

        $newFile = $this->_mediaDirectory->getAbsolutePath($this->_newFile);
        $processor->save($newFile);
        unset($processor);

        return $this;
    }

    /**
     * Check if file exists
     *
     * @param string $filename
     * @return bool
     */
    protected function fileExists($filename)
    {
        return $this->_mediaDirectory->isFile($filename);
    }

    /**
     * Return image url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function __toString(): string
    {
        $url = "";
        if ($this->_baseFile) {
            $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                $this->_newFile;
        }
        return $url;
    }
}
