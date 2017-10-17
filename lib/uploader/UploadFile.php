<?php

namespace Uploader;

/**
 * Class to work with a file
 *
 * @example
 * try {
 *     $NewFile = new UploadFile($_FILES['ImageFile']);
 *     $NewFile->upload();
 * } catch (Exception $e) {
 *     echo $e->getMessage();
 * }
 */
class UploadFile extends Uploader
{
    public function __construct($img)
    {
        if (!is_uploaded_file($img['tmp_name'])) {
            throw new Exception("Access denied. File wasn't uploaded");
        }

        if (isset($img['type'])) {
            $this->fileMimeType = mime_content_type($img['tmp_name']);
        }

        if (isset($_FILES['ImageFile']['size'])) {
            $this->fileSize = $img['size'];
        }

        if (isset($_FILES['ImageFile']['tmp_name'])) {
            $this->filePath = $img['tmp_name'];
        }

        $this->fileName = $img['name'];
    }

    private function saveFile()
    {
        $ext = pathinfo($this->fileName, PATHINFO_EXTENSION);

        $name = $this::UPLOAD_DIR . uniqid() . '.' . $ext;

        move_uploaded_file($this->filePath, $name);

        return $name;
    }

    public function upload()
    {
        $this->checkExtension();
        $this->checkSize();

        // Save temp file
        $filename = $this->saveFile();

        // Upload to cloud
        $storage = new \AWS\Storage();
        $imgID = $storage->uploadImage($filename, $filename);
        $imgURI = $storage->getImage($imgID);

        // Delete temp file
        unlink($filename);

        return $imgURI;
    }
}
