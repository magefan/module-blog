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

            $isPagebreakDefined = false;

            if (!$len) {
                $pageBraker = '<!-- pagebreak -->';
                $len = mb_strpos($content, $pageBraker);
                if ($len) {
                    $isPagebreakDefined = true;
                } else {
                    $len = (int)$this->scopeConfig->getValue(
                        'mfblog/post_list/shortcotent_length',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) ?: 2000;
                }
            }

            if ($len) {

                if (!$isPagebreakDefined) {

                    $oLen = $len;
                    /* Skip <style> tags at the begining of string in calculations */
                    $sp1 = mb_strpos($content, '<style>');
                    if (false !== $sp1) {
                        $stylePattern = "~\<style(.*)\>(.*)\<\/style\>~";
                        $cc = preg_replace($stylePattern, '', $content); /* remove style tag */
                        $sp2 = mb_strpos($content, '</style>');

                        while (false !== $sp1 && false !== $sp2 && $sp1 < $sp2 && $sp2 > $len && $sp1 < $len) {
                            $len = $oLen + $sp2 + 8;
                            $sp1 = mb_strpos($content, '<style>', $sp2 + 1);
                            $sp2 = mb_strpos($content, '</style>', $sp2 + 1);
                        }

                        $l = mb_strlen($content);
                        if ($len < $l) {
                            $sp2 = mb_strrpos($content, '</style>', $len - $l);
                            if ($len < $oLen + $sp2 + 8) {
                                $len = $oLen + $sp2 + 8;
                            }
                        }

                    } else {
                        $cc = $content;
                    }

                    /* Skip long HTML */
                    $stcc = trim(strip_tags((string)$cc));
                    //if ($stcc && strlen($stcc) < strlen($cc) / 3) {
                    if ($stcc && $len < mb_strlen($content)) {
                        $str = '';
                        $start = false;
                        foreach (explode(' ', $stcc) as $s) {
                            $str .= ($str ? ' ' : '') . $s;

                            $pos = mb_strpos($content, $str);
                            if (false !== $pos) {
                                $start = $pos;
                            } else {
                                break;
                            }
                        }

                        if (false !== $start) {
                            if ($len < $start + $oLen) {
                                $len = $start + $oLen;
                            }
                        }
                    }
                }

                /* Do not cut words */
                while ($len < strlen($content)
                    && !in_array($content[$len], [' ', '<', "\t", "\r", "\n"])) {
                    $len++;
                }

                $content = mb_substr($content, 0, $len);
                try {
                    $previousErrorState = libxml_use_internal_errors(true);
                    $dom = new \DOMDocument();
                    $dom->loadHTML('<?xml encoding="UTF-8">' . $content);
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


}
