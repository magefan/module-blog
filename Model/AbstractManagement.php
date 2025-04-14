<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Api\ManagementInterface;

/**
 * Abstract management model
 */
abstract class AbstractManagement implements ManagementInterface
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_itemFactory;

    protected $_imagePath = 'magefan_blog/';

    protected $_imagesMap = [
        'featured_img',
        'featured_list_img',
        'category_img',
        'tag_img'
    ];

    /**
     * Saves a file with unique name if necessary.
     *
     * @param string $fileName Desired file name (without path)
     * @param string $fileContent Content of the file
     * @return string Saved file name with short path
     */
    public function saveFile($fileName, $fileContent)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $file = $objectManager->get(\Magento\Framework\Filesystem\Driver\File::class);
        $directoryList = $objectManager->get(\Magento\Framework\Filesystem\DirectoryList::class);

        $targetDirectory = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . '/' . $this->_imagePath;
        $file->createDirectory($targetDirectory);

        $finalFileName = $this->getUniqueFileName($targetDirectory, $fileName, $file);
        $finalFilePath = $targetDirectory . $finalFileName;

        $file->filePutContents($finalFilePath, base64_decode($fileContent));

        return $this->_imagePath . $finalFileName;
    }

    /**
     * Generates a unique file name if the file already exists.
     *
     * @param $directory
     * @param $fileName
     * @param $fileDriver
     * @return string
     */
    protected function getUniqueFileName($directory, $fileName, $fileDriver)
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $counter = 1;

        $newFileName = $fileName;
        while ($fileDriver->isExists($directory . $newFileName)) {
            $newFileName = $name . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $newFileName;
    }

    /**
     * Create new item using data
     *
     * @param string $data
     * @return string || false
     */
    public function create($data)
    {
        try {
            $data = json_decode($data, true);
            foreach ($this->_imagesMap as $key) {
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
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * Update item using data
     *
     * @param int $id
     * @param string $data
     * @return string || false
     */
    public function update($id, $data)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->load($id);

            if (!$item->getId()) {
                return $this->getError('Item not found');
            }
            $data = json_decode($data, true);
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
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * Delete item by id
     *
     * @param  int $id
     * @return bool
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
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * Get item by id
     *
     * @param  int $id
     * @return bool
     */
    public function get($id)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->load($id);

            if (!$item->getId()) {
                return $this->getError('Item not found');
            }
            return json_encode($item->getData());
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * Get item by id and store id, only if item published
     *
     * @param  int $id
     * @param  int $storeId
     * @return bool
     */
    public function view($id, $storeId)
    {
        try {
            $item = $this->_itemFactory->create();
            $item->getResource()->load($item, $id);

            if (!$item->isVisibleOnStore($storeId)) {
                return $this->getError('Item is not visible on this store.');
            }

            return json_encode($this->getDynamicData($item));
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function getDynamicData($item);

    /**
     * @param $massage
     * @return false|string
     */
    public function getError($massage) {
        $data = ['error' => 'true'];
        
        $data['message'] = $massage ?? '';

        return json_encode($data);
    }
}
