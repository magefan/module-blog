<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post\View;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post view opengraph
 */
class Opengraph extends \Magefan\Blog\Block\Post\AbstractPost
{
    /**
     * Retrieve page type
     *
     * @return string
     */
    public function getType()
    {
        return $this->stripTags(
            $this->getPost()->getOgType()
        );
    }

    /**
     * Retrieve page title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->stripTags(
            $this->getPost()->getOgTitle()
        );
    }

    /**
     * Retrieve page short description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->stripTags(
            $this->getPost()->getOgDescription()
        );
    }

    /**
     * Retrieve page url
     *
     * @return string
     */
    public function getPageUrl()
    {
        return $this->stripTags(
            $this->getPost()->getPostUrl()
        );
    }

    /**
     * Retrieve page main image
     *
     * @return string | null
     */
    public function getImage()
    {
        $image = $this->getPost()->getOgImage();

        if (!$image) {
            $image = $this->getPost()->getFirstImage();
        }

        if ($image) {
            return $this->stripTags($image);
        }
    }
}
