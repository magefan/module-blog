<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Sitemap;


interface SitepamManagent
{
    /**
     * @param $page
     * @return bool
     */
    public function isEnabledSitemap($page);

    /**
     * @param $page
     * @return string
     */
    public function getFrequency($page);

    /**
     * @param $page
     * @return float
     */
    public function getPriority($page);

}