<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
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
    protected $imageHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
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
     * Retrieve image url
     * @return string
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
    public function resize(string $width, ?string $height = null)
    {
        return $this->imageHelper->init($this->getFile())
            ->resize($width, $height);
    }

    /**
     * Retrieve image url
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUrl();
    }
}
