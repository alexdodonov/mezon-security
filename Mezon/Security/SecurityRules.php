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
     * @param string $pathPrefix
     *            Prefix to file path
     * @return string File path
     * @codeCoverageIgnore
     */
    protected function prepareFs(string $pathPrefix): string
    {
        @mkdir($pathPrefix . '/data/');

        $path = '/data/files/';

        @mkdir($pathPrefix . $path);

        @mkdir($pathPrefix . $path . date('Y') . '/');

        @mkdir($pathPrefix . $path . date('Y') . '/' . date('m') . '/');

        $dir = $path . date('Y') . '/' . date('m') . '/' . date('d') . '/';

        @mkdir($pathPrefix . $dir);

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

        $fileName = md5((string) microtime(true));

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
     * @return string file's content of false in case of error
     * @codeCoverageIgnore
     */
    protected function fileGetContents(string $file)
    {
        $result = @file_get_contents($file);

        return $result === false ? '' : $result;
    }

    /**
     * Checking that file exists
     *
     * @param string $filePath
     *            path to the file
     * @return bool true if the file exists, false otherwise
     */
    private function fileExists(string $filePath): bool
    {
        return file_exists($filePath);
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
    public function storeFile(string $filePath, string $pathPrefix, bool $decoded = false): string
    {
        if ($this->fileExists($filePath)) {
            $fileContent = $this->fileGetContents($filePath);
        } else {
            throw (new \Exception('The file ' . $filePath . ' was not found'));
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
     * @param string $pathPrefix
     *            prefix of the file path
     * @return string|array Path to the stored file or the array $value itself
     */
    public function getFileValue($value, bool $storeFiles, string $pathPrefix = '.')
    {
        if (is_string($value)) {
            $value = $_FILES[$value];
        }

        if (isset($value['size']) && $value['size'] === 0) {
            return '';
        }

        if ($storeFiles) {
            $dir = '.' . $this->prepareFs($pathPrefix);

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
     * @param mixed $value
     *            Value to be made secure
     * @return string Secure value
     */
    public function getStringValue($value): string
    {
        return htmlspecialchars($value);
    }

    /**
     * Returning int value
     *
     * @param mixed $value
     *            Value to be made secure
     * @return int Secure value
     */
    public function getIntValue($value): int
    {
        return intval($value);
    }

    /**
     * Method validates uploaded file
     *
     * @param string $fieldName
     *            field in the $_FILES array
     * @param array $validators
     *            list of validators
     * @return bool true if the file valid and false otherwise.
     */
    public function isUploadedFileValid(string $fieldName, array $validators = []): bool
    {
        foreach ($validators as $validator) {
            $validator->setValidatingData($fieldName);

            if ($validator->valid() === false) {
                return false;
            }
        }

        return true;
    }
}
