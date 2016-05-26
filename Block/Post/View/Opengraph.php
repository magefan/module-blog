<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
    const TYPE = 'article';

    /**
     * Retrieve page type
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Retrieve page title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->stripTags(
            $this->getPost()->getTitle()
        );
    }

    /**
     * Retrieve page short description
     *
     * @return string
     */
    public function getDescription()
    {
        $content = trim($this->stripTags($this->getPost()->getContent()));
        $max = 300;
        if (mb_strlen($content) > $max) {
            $content = mb_substr($content, $max);
        }

        return $content;
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
        $image = $this->getPost()->getFeaturedImage();
        if (!$image) {
            $content = $this->getContent();
            $match = null;
            preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $match);
            if (!empty($match['src'])) {
                $image = $match['src'];
            }
        }

        if ($image) {
            return $this->stripTags($image);
        }

    }

}
