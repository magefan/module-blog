<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @var
     */
    protected $_baseFile;
    /**
     * @var
     */
    protected $_newFile;
    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param $baseFile
     * @return $this
     */
    public function init($baseFile)
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;
        return $this;
    }

    /**
     * @param $width
     * @param null $height
     * @return $this
     */
    public function resize($width, $height = null, $keepFrame = null)
    {
        if ($this->_baseFile) {
            $pathinfo = pathinfo(($this->_baseFile));
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
     * @param $width
     * @param $height
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
     * @param $filename
     * @return bool
     */
    protected function fileExists($filename)
    {
        return $this->_mediaDirectory->isFile($filename);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __toString()
    {
        $url = "";
        if ($this->_baseFile) {
            $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                $this->_newFile;
        }
        return $url;
    }
}
