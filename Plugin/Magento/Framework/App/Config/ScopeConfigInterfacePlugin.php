<?php

namespace Magefan\Blog\Plugin\Magento\Framework\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magefan\Blog\Model\Config;

class ScopeConfigInterfacePlugin
{
    /**
     * @var string[]
     */
    private $blogRoutes = [
        "index" => 'blog_index_index',
        "category" => 'blog_category_view',
        "post" => 'blog_post_view',
        "author" => 'blog_author_view',
        "archive" =>'blog_archive_view',
        'tag' => 'blog_tag_view'
    ];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        RequestInterface $request,
        Config $config
    )
    {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param $subject
     * @param $result
     * @param $path
     * @param $scopeType
     * @param $scopeCode
     * @return mixed|string
     */
    public function afterGetValue($subject, $result, $path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null) {
        if ($path == 'mageworx_seo/base/canonical/canonical_ignore_pages' &&  $this->request->getModuleName() == 'blog') {
            $blogPages = $this->config->getConfig(Config::XML_PATH_DISPLAY_CANONICAL_TAG_FOR);
            if ($blogPages == "all") {
                $result =  $result . PHP_EOL. implode(PHP_EOL, $this->blogRoutes);
            } else {
                $blogPages = explode(",", $blogPages);
                foreach ($blogPages as $page) {
                    if (isset($this->blogRoutes[$page])) {
                        $result .= PHP_EOL . $this->blogRoutes[$page];
                    }
                }
            }
        }
        return $result;
    }
}