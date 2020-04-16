<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

/**
 * Blog post gallery content
 */
namespace Magefan\Blog\Block\Adminhtml\Post\Helper\Form\Gallery;

use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;

class Content extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'post/helper/gallery.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var ImageUploadConfigDataProvider
     */
    private $imageUploadConfigDataProvider;

    /**
     * Content constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @param null $imageUploadConfigDataProvider
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = [],
        $imageUploadConfigDataProvider = null
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
        try {
            /* Try for old magento version where ImageUploadConfigDataProvider does not exist */
            if (class_exists(\Magento\Backend\Block\DataProviders\ImageUploadConfig::class)) {
                $this->imageUploadConfigDataProvider = $imageUploadConfigDataProvider
                    ?: ObjectManager::getInstance()->get(\Magento\Backend\Block\DataProviders\ImageUploadConfig::class);
            } elseif (class_exists(\Magento\Backend\Block\DataProviders\UploadConfig::class)) {
                /* Workaround for Magento 2.2.8 */
                $this->imageUploadConfigDataProvider = ObjectManager::getInstance()->get(
                    \Magento\Backend\Block\DataProviders\UploadConfig::class
                );
            }
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'uploader',
            \Magento\Backend\Block\Media\Uploader::class,
            ['image_upload_config_data' => $this->imageUploadConfigDataProvider]
        );

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->getUrl('blog/post_upload/gallery')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        $this->_eventManager->dispatch('blog_post_gallery_prepare_layout', ['block' => $this]);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        $value = $this->getElement()->getImages();
        if (is_array($value) &&
            array_key_exists('images', $value) &&
            is_array($value['images']) &&
            count($value['images'])
        ) {
            $images = $this->sortImagesByPosition($value['images']);

            return $this->_jsonEncoder->encode($images);
        }
        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }
}
