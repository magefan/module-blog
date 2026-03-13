<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Post\View\Comments;

use Magefan\Blog\Model\Config\Source\CommetType;

/**
 * Blog post Facebook comments block
 */
class Facebook extends \Magefan\Blog\Block\Post\View\Comments
{
    /**
     * @var string
     */
    protected $commetType = CommetType::FACEBOOK;

    /**
     * Constructs and returns the URL for loading the Facebook SDK JavaScript.
     *
     * @return string
     */
    public function getFbSdkJsUrl(): string
    {
        return '//connect.facebook.net/'.
            $this->getLocaleCode() . '/sdk.js#xfbml=1&version=v3.3&appId=' .
            $this->getFacebookAppId() . '&autoLogAppEvents=1';
    }
}
