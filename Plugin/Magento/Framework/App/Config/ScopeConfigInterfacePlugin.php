<?php

namespace Magefan\Blog\Plugin\Magento\Framework\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class ScopeConfigInterfacePlugin
{
    /**
     * @var string[]
     */
    protected $blogRoutes = [
        'blog_index_index',
        'blog_category_view',
        'blog_post_view',
        'blog_author_view',
        'blog_archive_view',
        'blog_tag_view'
    ];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
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
            return $result . ' ' . implode(' ', $this->blogRoutes);
        }
        return $result;
    }
}