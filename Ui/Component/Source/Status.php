<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Ui\Component\Source;

use Magefan\Blog\Model\Post;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Post status option source
 */
class Status implements OptionSourceInterface
{
    /**
     * @var Post
     */
    private $post;

    /**
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->post->getAvailableStatuses() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }
}