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
 * Blog post view rich snippets
 */
class Richsnippets extends Opengraph
{
    /**
     * @param array
     */
    protected $_options;


    /**
     * Retrieve snipet params
     *
     * @return array
     */
    public function getOptions(): array
    {
        if (null === $this->_options) {
            $post = $this->getPost();
            $snippetOption = $post->getData('structure_data_type');
            switch ($snippetOption) {
                case '1':
                    $this->_options = $this->getOptionsByType('NewsArticle');
                    break;
                case '2':
                    $this->_options = [];
                    break;
                case '3':
                    $this->_options = $this->getOptionsByType('Article');
                    break;
                default:
                    $this->_options = $this->getOptionsByType('BlogPosting');
                    break;
            }
        }
        return $this->_options;
    }

    /**
     * @param $type
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionsByType($type)
    {
        $post = $this->getPost();

        $logoBlock = $this->getLayout()->getBlock('logo');
        if (!$logoBlock) {
            $logoBlock = $this->getLayout()->getBlock('amp.logo');
        }

        $options = [
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
                'url' => $this->getImage() ?:
                    ($logoBlock ? $logoBlock->getLogoSrc() : '')
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

        return $options;
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

                $result = [
                    '@context' => 'http://schema.org',
                    '@type' => 'Person',
                    'name' => $author->getTitle(),
                    'url' => $authorPageEnabled ? $author->getAuthorUrl() : $this->getUrl(),
                    'mainEntityOfPage' => [
                        '@type' => 'WebPage',
                        '@id' => $authorPageEnabled ? $author->getAuthorUrl() : $this->getUrl(),
                    ]
                ];

                $sameAs = [];
                foreach (['facebook_page_url', 'twitter_page_url', 'instagram_page_url', 'googleplus_page_url', 'linkedin_page_url'] as $key) {
                    if ($value = trim($author->getData($key) ?: '')) {
                        $sameAs[] = $value;
                    }
                }

                if ($sameAs) {
                    $result['sameAs'] = $sameAs;
                }

                if ($value = trim($author->getData('role') ?: '')) {
                    $result['jobTitle'] = $value;
                }

                return $result;
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
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $options = $this->getOptions();
        if (!$options) {
            return '';
        }
        return '<script type="application/ld+json">'
            . json_encode($options)
            . '</script>';
    }


    /**
     * Retrieve page title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->stripTags(
            $this->getPost()->getMetaTitle()
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
            $this->getPost()->getMetaDescription()
        );
    }

    /**
     * Retrieve page main image
     *
     * @return string | null
     */
    public function getImage()
    {
        $image = null;

        if (!$image) {
            $image = $this->getPost()->getFeaturedImage();
        }

        if (!$image) {
            $image = $this->getPost()->getFirstImage();
        }

        if ($image) {
            return $this->stripTags($image);
        }
    }
}
