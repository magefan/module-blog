<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Archive;

trait Archive
{
    /**
     * Get archive month
     * @return int
     */
    public function getMonth()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_month');
    }

    /**
     * Get archive year
     * @return int
     */
    public function getYear()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_year');
    }


    /**
     * @param string $content
     * @return string
     */
    private function filterContent(string $content):string
    {
        if (!$content) {
            return '';
        }
        $vars = ['year', 'month'];
        foreach ($vars as $var) {
            $schemaVar = '{{' . $var . '}}';
            if (strpos($content, $schemaVar) !== false) {
                $value = '';
                switch ($var) {
                    case 'year':
                        $value = date('Y', strtotime($this->getYear() . '-01-01'));
                        break;
                    case 'month':
                        if ($this->getMonth()) {
                            $value = date('F', strtotime($this->getYear() . '-' . $this->getMonth() . '-01'));
                        }
                        break;
                }
                $content = str_replace($schemaVar, $value, $content);
            }
        }
        return $content;
    }
}
