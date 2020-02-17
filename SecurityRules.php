<?php
namespace Mezon\Security;

/**
 * Class SecurityRules
 *
 * @package Security
 * @subpackage SecurityRules
 * @author Dodonov A.A.
 * @version v.1.0 (2020/01/13)
 * @copyright Copyright (c) 2020, aeon.org
 */

/**
 * Security rules class
 *
 * @author Dodonov A.A.
 */
class SecurityRules
{

    /**
     * Method prepares file system for saving file
     *
     * @param string $filePrefix
     *            Prefix to file path
     * @return string File path
     * @codeCoverageIgnore
     */
    protected function prepareFs(string $filePrefix): string
    {
        @mkdir($filePrefix . '/data/');

        $path = '/data/files/';

        @mkdir($filePrefix . $path);

        @mkdir($filePrefix . $path . date('Y') . '/');

        @mkdir($filePrefix . $path . date('Y') . '/' . date('m') . '/');

        $dir = $path . date('Y') . '/' . date('m') . '/' . date('d') . '/';

        @mkdir($filePrefix . $dir);

        return $dir;
    }

    /**
     * Method stores file on disk
     *
     * @param string $file
     *            file path
     * @param string $content
     *            file content
     * @codeCoverageIgnore
     */
    protected function filePutContents(string $file, string $content): void
    {
        file_put_contents($file, $content);
    }

    /**
     * Method stores file on disk
     *
     * @param string $fileContent
     *            Content of the saving file
     * @param string $pathPrefix
     *            Prefix to file
     * @param bool $decoded
     *            If the file was not encodded in base64
     * @return string Path to file
     */
    public function storeFileContent(string $fileContent, string $pathPrefix, bool $decoded = false): string
    {
        $dir = $this->prepareFs($pathPrefix);

        $fileName = md5(microtime(true));

        if ($decoded) {
            $this->filePutContents($pathPrefix . $dir . $fileName, $fileContent);
        } else {
            $this->filePutContents($pathPrefix . $dir . $fileName, base64_decode($fileContent));
        }

        return $dir . $fileName;
    }

    /**
     * Method returns file's content of false in case of error
     *
     * @param string $file
     *            path to the loading file
     * @return string|bool file's content of false in case of error
     * @codeCoverageIgnore
     */
    protected function fileGetContents(string $file)
    {
        return @file_get_contents($file);
    }

    /**
     * Method stores file on disk
     *
     * @param string $filePath
     *            Path to the saving file
     * @param string $pathPrefix
     *            Prefix to file
     * @param bool $decoded
     *            If the file was not encodded in base64
     * @return string Path to file or null if the image was not loaded
     */
    public function storeFile(string $filePath, string $pathPrefix, bool $decoded = false): ?string
    {
        $fileContent = $this->fileGetContents($filePath);

        if ($fileContent === false) {
            return null;
        }

        return $this->storeFileContent($fileContent, $pathPrefix, $decoded);
    }

    /**
     * Method stores uploaded file
     *
     * @param string $from
     *            path to the uploaded file
     * @param string $to
     *            destination file path
     * @codeCoverageIgnore
     */
    protected function moveUploadedFile(string $from, string $to): void
    {
        move_uploaded_file($from, $to);
    }

    /**
     * Method returns file value
     *
     * @param mixed $value
     *            Data about the uploaded file
     * @param bool $storeFiles
     *            Must be the file stored in the file system of the service or not
     * @return string|array Path to the stored file or the array $value itself
     */
    public function getFileValue($value, bool $storeFiles)
    {
        if (is_string($value)) {
            $value = $_FILES[$value];
        }

        if (isset($value['size']) && $value['size'] === 0) {
            return '';
        }

        if ($storeFiles) {
            $dir = '.' . $this->prepareFs('.');

            $uploadFile = $dir . md5($value['name'] . microtime(true)) . '.' .
                pathinfo($value['name'], PATHINFO_EXTENSION);

            if (isset($value['file'])) {
                $this->filePutContents($uploadFile, base64_decode($value['file']));
            } else {
                $this->moveUploadedFile($value['tmp_name'], $uploadFile);
            }

            return $uploadFile;
        } else {
            return $value;
        }
    }

    /**
     * Returning string value
     *
     * @param string $value
     *            Value to be made secure
     * @return string Secure value
     * @codeCoverageIgnore
     */
    public function getStringValue(string $value): string
    {
        return htmlspecialchars($value);
    }
}