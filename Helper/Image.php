<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $_width;
    protected $_height;

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 100;
    protected $_keepAspectRatio = false;
    protected $_keepFrame = false;
    protected $_keepTransparency = true;
    protected $_constrainOnly = true;
    protected $_backgroundColor = [255, 255, 255];
    protected $_baseFile;
    protected $_isBaseFilePlaceholder;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;
    protected $_watermarkFile;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeight;
    protected $_watermarkImageOpacity = 0;

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

    public function init($baseFile)
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;
        return $this;
    }

    public function getImageProcessor()
    {
        //if (!$this->_processor) {
            $filename = $this->_baseFile ? $this->_mediaDirectory->getAbsolutePath($this->_baseFile) : null;
            $this->_processor = $this->_imageFactory->create($filename);
        //}
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        $this->_processor->resize($this->_width, $this->_height);
        return $this->_processor;
    }

    public function saveFile()
    {
        $filename = $this->_mediaDirectory->getAbsolutePath($this->_newFile);
        $this->getImageProcessor()->save($filename);
        //$this->_coreFileStorageDatabase->saveFile($filename);
        return $this;
    }

    protected function _fileExists($filename)
    {
        if ($this->_mediaDirectory->isFile($filename)) {
            return true;
        } else {
            return false;
        }
    }

    public function isCached()
    {
        if (is_string($this->_newFile)) {
            return $this->_fileExists($this->_newFile);
        }
    }

    public function resize($width,$height = null)
    {
    	if($this->_baseFile){
			$this->_width = $width;
			$this->_height = $height;
			if(!$this->isCached()){
				$path = 'blog/cache/'.$width.'x'.$height;
				$this->_newFile = $path. '/' . $this->_baseFile;
				$this->saveFile();
		    }
        }
        return $this;
    }

    public function __toString()
    {
    	$url = "";
    	if($this->_baseFile){
			$url = $this->_storeManager->getStore()->getBaseUrl(
				    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
				) . $this->_newFile;
	    }
		return $url;
    }
}
