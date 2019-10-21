<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Post\Tag;

use \Magento\Framework\Registry;
use \Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;

/**
 * Class Tag Autocomplete Block
 */
class Autocomplete extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Autocomplete constructor.
     * @param Context $context
     * @param array $data
     * @param Registry $registry
     */
    public function __construct(Context $context, array $data = [], Registry $registry)
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * @return bool|false|string
     */
    public function getLinkedTags()
    {
        $post = $this->registry->registry('current_model');
        if ($post) {
            $tagsCollection = $post->getRelatedTags();
            $tagsTitles = [];
            foreach ($tagsCollection as $tag) {
                $tagsTitles[] = $tag->getData('title');
            }
            $tagsTitles = array_unique($tagsTitles);
        } else {
            $tagsTitles = [];
        }
        return json_encode($tagsTitles);
    }

    /**
     * @return string
     */
    public function getAutocompleteUrl()
    {
        return $this->getUrl('blog/tag/autocomplete');
    }
}
