<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model;

/**
 * Blog Template Pool
 *
 * @api
 * @since 2.10.0
 */
class TemplatePool
{
    /**
     * Templates objects
     *
     * @var []
     */
    private $templates;

    /**
     * TemplatePool constructor.
     * @param array $templates
     */
    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * Retrieve all templates for type
     * @param $templateType
     * @return array
     */
    public function getAll(string $templateType):array
    {
        return $this->templates[$templateType] ?? [];
    }

    /**
     * Retrieve template
     * @param string $templateType
     * @param string $name
     * @return string
     */
    public function getTemplate(string $templateType, string $name):string
    {
        if (isset($this->templates[$templateType]) &&
            (is_array($this->templates[$templateType]) || $this->templates[$templateType] instanceof Countable)
        ) {
            foreach ($this->templates[$templateType] as $item) {
                if (isset($item['value']) && $item['value'] == $name) {
                    return $item['template'];
                }
            }
        }
        return '';
    }
}