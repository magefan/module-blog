<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Api\ShortContentExtractorInterface;

class ShortContentExtractor implements ShortContentExtractorInterface
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var array
     */
    private $executedContent = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null
    ) {
        $this->filterProvider = $filterProvider;
        $this->scopeConfig = $scopeConfig ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * Retrieve short filtered content
     * @param string $content
     * @param mixed $len
     * @param mixed $endCharacters
     * @return string
     * @throws \Exception
     */
    public function execute($content, $len = null, $endCharacters = null)
    {
        $content = (string)$content;

        $key = md5($content) . $len . $endCharacters;
        if (!isset($this->executedContent[$key])) {

            $content = $this->filterProvider->getPageFilter()->filter(
                (string) $content ?: ''
            );

            $content = $this->setPageBreakOnLen($len, $content);

            if (!$len) {
                $pageBraker = '<!-- pagebreak -->';
                $len = mb_strpos($content, $pageBraker);
                if(!$len) {
                    $len = (int)$this->scopeConfig->getValue(
                        'mfblog/post_list/shortcotent_length',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) ?: 2000;
                }
            }

            if ($len) {
                /* Do not cut words */
                while ($len < mb_strlen($content)
                    && !in_array(mb_substr($content, $len, 1), [' ', '<', "\t", "\r", "\n"])) {
                    $len++;
                }

                $content = mb_substr($content, 0, $len);
                try {
                    $previousErrorState = libxml_use_internal_errors(true);
                    $dom = new \DOMDocument();
                    $dom->loadHTML('<?xml encoding="UTF-8">' . '<body>' . $content . '</body>');
                    libxml_use_internal_errors($previousErrorState);

                    $body = $dom->getElementsByTagName('body');
                    if ($body && $body->length > 0) {
                        $body = $body->item(0);
                        $_content = new \DOMDocument;
                        foreach ($body->childNodes as $child) {
                            $_content->appendChild($_content->importNode($child, true));
                        }
                        $content = $_content->saveHTML();
                    }
                } catch (\Exception $e) {
                    /* Do nothing, it's OK */
                }
            }

            if ($endCharacters === null) {
                $endCharacters = $this->scopeConfig->getValue(
                    'mfblog/post_list/end_characters',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }

            if ($len && $endCharacters) {
                $trimMask = " \t\n\r\0\x0B,.!?";
                if ($p = strrpos($content, '</')) {
                    $p2 = $p;
                    do {
                        $p = $p2;
                        $p2 = strrpos($content, '</', $p - strlen($content) - 1);
                    } while ($p2 && $p - $p2 <= 6);

                    $content = trim(substr($content, 0, $p), $trimMask)
                        . $endCharacters
                        . substr($content, $p);
                } else {
                    $content = trim($content, $trimMask)
                        . $endCharacters;
                }
            }

            $this->executedContent[$key] = $content;
        }

        return $this->executedContent[$key];
    }

    /**
     * @param $len
     * @param string $content
     * @return string
     */
    protected function setPageBreakOnLen($len, string $content): string
    {
        if (!$len) {
            $len = (int)$this->scopeConfig->getValue(
                'mfblog/post_list/shortcotent_length',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) ?: 2000;
        }

        $content = str_replace('<!-- pagebreak -->', '', $content);

        $previousErrorState = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->loadHTML('<?xml encoding="UTF-8">' . '<body>' . $content . '</body>');
        libxml_use_internal_errors($previousErrorState);

        $textLength = 0;

        $processNode = function($node) use (&$textLength, $len, $dom, &$pageBreakInserted, &$processNode) {
            foreach ($node->childNodes as $child) {
                $processNode($child);
            }

            if ($node->nodeType === XML_TEXT_NODE) {

                $text = $node->nodeValue;
                $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                $newText = '';

                foreach ($words as $word) {
                    if (trim($word) === '') {
                        $newText .= $word;
                        continue;
                    }

                    $newText .= $word;

                    if ($textLength + mb_strlen($word, 'utf-8') >= $len && !$pageBreakInserted) {
                        $newText .= '<!-- pagebreak -->';
                        $pageBreakInserted = true;
                    }

                    $textLength += mb_strlen($word, 'utf-8');
                }

                $node->nodeValue = $newText;
            }
        };

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $processNode($body);
        }

        $content = $dom->saveHTML($dom->documentElement);
        return str_replace( '&lt;!-- pagebreak --&gt;', '<!-- pagebreak -->', $content);
    }
}
