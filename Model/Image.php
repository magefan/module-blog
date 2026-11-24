<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model;

use Magefan\Blog\Model\Url;
use Magefan\Blog\Helper\Image as ImageHelper;

/**
 * Image model
 *
 * @method string getFile()
 * @method $this setFile(string $value)
 */
class Image extends \Magento\Framework\DataObject
{
    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $url;
    /**
     * @var \Magefan\Blog\Helper\Image
     */
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @param \Magefan\Blog\Model\Url $url
     * @param ImageHelper $imageHelper
     * @param array $data
     */
    public function __construct(
        Url $url,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->url = $url;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get the media URL of the file
     *
     * @return string|null
     */
    public function getUrl()
    {
        if ($this->getFile()) {
            return $this->url->getMediaUrl($this->getFile());
        }

        return null;
    }
    /**
     * Resize image
     * @param string $width
     * @param string|null $height
     * @return string
     */
    /**
     * Resize the image to the specified dimensions
     *
     * @param string $width
     * @param string|null $height
     * @return mixed
     */
    public function resize(string $width, ?string $height = null)
    {
        return $this->imageHelper->init($this->getFile())
            ->resize($width, $height);
    }

    /**
     * Retrieve image url
     * @return string
     */
    /**
     * Converts the object to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUrl();
    }
}
