<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model;

use Magefan\Blog\Api\ManagementInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract management model
 */
abstract class AbstractManagement implements ManagementInterface
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_itemFactory;

    /**
     * @var string
     */
    protected $_imagePath = 'magefan_blog/';

    /**
     * @var string[]
     */
    protected $_imagesMap = [
        'featured_img',
        'featured_list_img',
        'og_img',
        'category_img',
        'tag_img'
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Save base64-encoded image file to media directory
     *
     * @param string $fileName
     * @param string $fileContent
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function saveFile(string $fileName, string $fileContent): string
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get(Filesystem::class);

        $fileIo = $objectManager->get(\Magento\Framework\Filesystem\Io\File::class);

        $pathInfo  = $fileIo->getPathInfo($fileName);
        $extension = strtolower((string)($pathInfo['extension'] ?? ''));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            throw new LocalizedException(__('File type "%1" is not allowed.', $extension));
        }

        /* Replace dots in the name to prevent double extensions like "shell.php.png" */
        $name = str_replace('.', '_', (string)($pathInfo['filename'] ?? ''));
        if ($name === '') {
            $name = 'image';
        }
        $safeName = $name . '.' . $extension;

        $mediaDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDir->create($this->_imagePath);

        $finalFileName = $this->getUniqueFileName($mediaDir, $this->_imagePath, $safeName);
        $relativePath  = $this->_imagePath . $finalFileName;

        $decoded = base64_decode($fileContent, true); // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if ($decoded === false) {
            throw new LocalizedException(__('Invalid file content encoding.'));
        }

        $imageInfo = getimagesizefromstring($decoded); // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if ($imageInfo === false) {
            throw new LocalizedException(__('Uploaded file is not a valid image.'));
        }

        $mediaDir->writeFile($relativePath, $decoded);

        return $relativePath;
    }

    /**
     * Generates a unique file name if the file already exists.
     *
     * @param \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDir
     * @param string $path Relative directory path within media
     * @param string $fileName
     * @return string
     */
    protected function getUniqueFileName($mediaDir, string $path, string $fileName): string
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        // phpcs:enable
        $counter = 1;

        $newFileName = $fileName;
        while ($mediaDir->isExist($path . $newFileName)) {
            $newFileName = $name . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $newFileName;
    }

    /**
     * Create new item using data
     *
     * @param string $data
     * @return string
     */
    public function create($data)
    {
        try {
            $data = json_decode($data, true);
            foreach ($this->_imagesMap as $key) {
                /* Image path can be set only via file upload (<key>_name + <key>_content), empty value clears it */
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
                if (empty($data[$key . '_name']) || empty($data[$key . '_content'])) {
                    unset($data[$key . '_name']);
                    unset($data[$key . '_content']);
                    continue;
                }
                $data[$key] = $this->saveFile($data[$key . '_name'], $data[$key . '_content']);
            }

            $item = $this->_itemFactory->create();
            $item->setData($data)->save();
            return json_encode($item->getData());
        } catch (LocalizedException $e) {
            return $this->getError($e->getMessage());
        } catch (\Throwable $e) {
            $this->getLogger()->critical($e);
            return $this->getError('An error occurred while creating the item.');
        }
    }

    /**
     * Update item using data
     *
     * @param int $id
     * @param string $data
     * @return string
     */
    public function update($id, $data)
    {
        try {
            $data = json_decode($data, true);
            foreach ($this->_imagesMap as $key) {
                /* Image path can be set only via file upload (<key>_name + <key>_content), empty value clears it */
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }

            $item = $this->_itemFactory->create();

            if (!empty($data['store_id'])) {
                $item->setStoreId((int)$data['store_id']);
                $item->setData('data_to_update', $data);
            } else {
                $storeId = 0;

                if ($storeIdFromRequest = $this->getStoreIdFromRestRequest()) {
                    $item->setData('data_to_update', $data);
                    $storeId = $storeIdFromRequest;
                }

                $item->setStoreId($storeId);
            }

            $item->load($id);

            if (!$item->getId()) {
                return $this->getError('Item not found');
            }

            foreach ($this->_imagesMap as $key) {
                if (empty($data[$key . '_name']) || empty($data[$key . '_content'])) {
                    unset($data[$key . '_name']);
                    unset($data[$key . '_content']);
                    continue;
                }
                $data[$key] = $this->saveFile($data[$key . '_name'], $data[$key . '_content']);
            }

            $item->addData($data)->save();
            return json_encode($item->getData());
        } catch (LocalizedException $e) {
            return $this->getError($e->getMessage());
        } catch (\Throwable $e) {
            $this->getLogger()->critical($e);
            return $this->getError('An error occurred while updating the item.');
        }
    }

    /**
     * Delete item by id
     *
     * @param  int $id
     * @return bool|string
     */
    public function delete($id)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->load($id);
            if ($item->getId()) {
                $item->delete();
                return true;
            }
            return $this->getError('Something went wrong');
        } catch (LocalizedException $e) {
            return $this->getError($e->getMessage());
        } catch (\Throwable $e) {
            $this->getLogger()->critical($e);
            return $this->getError('An error occurred while deleting the item.');
        }
    }

    /**
     * Get item by id
     *
     * @param  int $id
     * @param  int|null $storeId
     * @return bool
     */
    public function get($id, $storeId = 0)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->setStoreId((int)$storeId);
            $item->load($id);

            if (!$item->getId()) {
                return $this->getError('Item not found');
            }
            return json_encode($item->getData());
        } catch (LocalizedException $e) {
            return $this->getError($e->getMessage());
        } catch (\Throwable $e) {
            $this->getLogger()->critical($e);
            return $this->getError('An error occurred while loading the item.');
        }
    }

    /**
     * Get item by id and store id, only if item published
     *
     * @param  int $id
     * @param  int $storeId
     * @return string
     */
    public function view($id, $storeId)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->setStoreId((int)$storeId);
            // phpcs:ignore
            $item->getResource()->load($item, $id);

            if (!$item->isVisibleOnStore($storeId)) {
                return $this->getError('Item is not visible on this store.');
            }

            $data = $this->getDynamicData($item);
            return json_encode($data);
        } catch (LocalizedException $e) {
            return $this->getError($e->getMessage());
        } catch (\Throwable $e) {
            $this->getLogger()->critical($e);
            return $this->getError('An error occurred while loading the item.');
        }
    }

    /**
     * Get dynamic data for item
     *
     * @param mixed $item
     * @return mixed
     */
    abstract protected function getDynamicData($item);

    /**
     * Remove sensitive fields from response data.
     *
     * @param array $data
     * @return array
     */
    protected function unsetSensitiveData($data)
    {
        unset(
            $data['draft_content'],
            $data['published_content'],
            $data['auto_save_data'],
            $data['secret']
        );

        return $data;
    }

    /**
     * Error response
     *
     * @param string $massage
     * @return false|string
     */
    public function getError($massage)
    {
        $data = ['error' => 'true'];

        $data['message'] = $massage ?? '';

        return json_encode($data);
    }

    /**
     * If request url contains store code - get store id
     *
     * @return int
     */
    private function getStoreIdFromRestRequest(): int
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $appState = $objectManager->get(State::class);
        $request = $objectManager->get(RequestInterface::class);
        $storeManager = $objectManager->get(StoreManagerInterface::class);

        $storeId = 0;

        if (Area::AREA_WEBAPI_REST == $appState->getAreaCode()) {
            $storeCode = $storeManager->getStore()->getCode();

            // /rest/ua/V1/blog/post/2 or /ua/rest/V1/blog/post/2
            $path = explode('/', trim($request->getRequestUri(), '/'));

            if ('rest' === $path[0] && $path[1] === $storeCode
                || 'rest' === $path[1] && $path[0] === $storeCode
            ) {
                $storeId = (int)$storeManager->getStore()->getId();
            }
        }

        return $storeId;
    }

    /**
     * Get logger instance
     *
     * @return mixed|LoggerInterface
     */
    protected function getLogger()
    {
        if (null == $this->logger) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(LoggerInterface::class);
        }
        return $this->logger;
    }
}
