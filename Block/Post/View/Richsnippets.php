<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post\View;

use Magento\Framework\Exception\LocalizedException;
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
     * @return array|mixed|null
     */
    public function getOptions()
    {
        $post = $this->getPost();
        $structureData = $post->getData('structure_data');
        return $structureData;
    }

    /**
     * Retrieve snipet params
     *
     * @return array
     * @throws LocalizedException
     */
    public function getBlogPostOptions()
    {
        if ($this->_options === null) {
            $post = $this->getPost();

            $logoBlock = $this->getLayout()->getBlock('logo');
            if (!$logoBlock) {
                $logoBlock = $this->getLayout()->getBlock('amp.logo');
            }
            $snippetOption = $this->getOptions();

            switch ($snippetOption) {
                case '1':
                    $type = 'NewsArticle';
                    break;
                case '2':
                    $type = null;
                    break;
                default:
                    $type = 'BlogPosting';
                    break;
            }
            if ($type != null){
                $this->_options = [
                    '@context' => 'http://schema.org',
                    '@type' => $type,
                    '@id' => $post->getPostUrl(),
                    'author' => $this->getAuthor(),
                    'headline' => $this->getTitle(),
                    'description' => $this->getDescription(),
                    'datePublished' => $post->getPublishDate('c'),
                    'dateModified' => $post->getUpdateDate('c'),
                    'image' => [
                        '@type' => 'ImageObject',
                        'url' => $this->getImage() ?: ($logoBlock ? $logoBlock->getLogoSrc() : ''),
                        'width' => 720,
                        'height' => 720,
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => $this->getPublisher(),
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $logoBlock ? $logoBlock->getLogoSrc() : '',
                        ],
                    ],
                    'mainEntityOfPage' => $this->_url->getBaseUrl(),
                ];
            }

        }
        var_dump($this->_options);exit();
        return $this->_options;
    }

    /**
     * Retrieve author name
     *
     * @return array
     */
    public function getAuthor()
    {
        if ($author = $this->getPost()->getAuthor()) {
            if ($author->getTitle()) {
                $authorPageEnabled = $this->config->getConfig(
                    'mfblog/author/page_enabled'
                );
                return [
                    '@type' => 'Person',
                    'name' => $author->getTitle(),
                    'url' => $authorPageEnabled ? $author->getAuthorUrl() : $this->getUrl()
                ];
            }
        }

        // if no author name return name of publisher
        return $this->getPublisher();
    }

    /**
     * Retrieve publisher name
     *
     * @return array
     */
    public function getPublisher()
    {
        $publisherName =  $this->_scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );

        if (!$publisherName) {
            $publisherName = 'Magento2 Store';
        }

        return $publisherName;

        /*
        return [
            '@type' => 'Organization',
            'name' => $publisherName,
            'url' => $this->getUrl()
        ];
        */
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if(empty($this->getBlogPostOptions())){
            return '';
        }
        return '<script type="application/ld+json">'
            . json_encode($this->getBlogPostOptions())
            . '</script>';
    }
}
