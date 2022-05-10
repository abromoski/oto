<?php

namespace Etatvasoft\Banner\Controller\Adminhtml\Banner;

use Etatvasoft\Banner\Model\Banner;

/**
 * Class save
 * @package Etatvasoft\Banner\Controller\Adminhtml\Banner
 */
class Save extends \Etatvasoft\Banner\Controller\Adminhtml\Banner
{
    /**
     * @param $model
     * @param $request
     */
    protected function _beforeSave($model, $request)
    {

        $data = $model->getData();
        $model->setData($data);

        /* prepare banner image */
        $imageField = 'banner_image';
        $fileSystem = $this->_objectManager->create('Magento\Framework\Filesystem');
        $mediaDirectory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        if (isset($data[$imageField]) && isset($data[$imageField]['value'])) {
            if (isset($data[$imageField]['delete'])) {
                unlink($mediaDirectory->getAbsolutePath() . $data[$imageField]['value']);
                $model->setData($imageField, '');
            } else {
                $model->unsetData($imageField);
            }
        }

        try {
            $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\UploaderFactory');
            $uploader = $uploader->create(['fileId' => 'post['.$imageField.']']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath(Banner::BASE_MEDIA_PATH)
            );
            $model->setData($imageField, Banner::BASE_MEDIA_PATH . $result['file']);
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                $this->messageManager->addException($e, __('Please Insert Image of types jpg, jpeg, gif, png'));
            }
        }
    }
}
