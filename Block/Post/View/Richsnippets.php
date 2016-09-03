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
 * Blog post view rich snippets
 */
class Richsnippets extends Opengraph
{
    /**
     * @param  array
     */
    protected $_options;

    /**
     * Retrieve snipet params
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->_options === null) {
            $post = $this->getPost();

            $logoBlock = $this->getLayout()->getBlock('logo');
            $author = $this->getAuthor();

            $this->_options = array(
                '@context' => 'http://schema.org',
                '@type' => 'BlogPosting',
                '@id' => $post->getPageUrl(),
                'author' => $author,
                'headline' => $this->getTitle(),
                'description' => $this->getDescription(),
                'datePublished' => $post->getPublishDate('c'),
                'dateModified' => $post->getUpdateDate('c'),
                'image' => [
                    '@type' => 'ImageObject',
                    'url' => $this->getImage() ?:
                        ($logoBlock ? $logoBlock->getLogoSrc() : ''),
                    'width' => 720,
                    'height' => 720,
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $author,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $logoBlock ? $logoBlock->getLogoSrc() : '',
                    ],
                ],
            );
        }

        return $this->_options;
    }

    /**
     * Retrieve author name
     *
     * @return array
     */
    public function getAuthor()
    {
        $author =  $this->_scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );

        if (!$author) {
            $author = 'Magento2 Store';
        }

        return $author;
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '<script type="application/ld+json">'
            . json_encode($this->getOptions())
            . '</script>';
    }
}
