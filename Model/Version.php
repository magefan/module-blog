<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model;

use Magefan\Community\Api\GetModuleVersionInterface;
use Magefan\Blog\Api\VersionInterface;

/**
 * Version model
 */
class Version implements VersionInterface
{
    /**
     * @var GetModuleVersionInterface
     */
    protected $getModuleVersion;

    /**
     * Version constructor.
     * @param GetModuleVersionInterface $getModuleVersion
     */
    public function __construct(
        GetModuleVersionInterface $getModuleVersion
    ) {
        $this->getModuleVersion = $getModuleVersion;
    }

    /**
     * @return false|string
     */
    public function getVersion(): string
    {
        try {
            $moduleName = 'Blog';
            $currentVersion = $this->getModuleVersion->execute($moduleName);
            $edition = 'Basic';
            foreach (['Extra', 'Plus'] as $_edition) {
                if ($_currentVersion = $this->getModuleVersion->execute($moduleName . $_edition)) {
                    $edition = $_edition;
                    $currentVersion = $_currentVersion;
                    break;
                }
            }

            $data = ['version' => $currentVersion, 'edition' => $edition];
            return json_encode($data);
        } catch (\Exception $e) {
            return json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }
}
