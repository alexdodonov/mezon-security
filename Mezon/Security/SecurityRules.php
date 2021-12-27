<?php
namespace Mezon\Security;

use Mezon\Fs\Layer;
use Mezon\Security\Validators\ValidatorInterface;

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
     *            prefix to file path
     * @return string file path
     */
    private function prepareFs(string $pathPrefix): string
    {
        Layer::createDirectory($pathPrefix . '/Data/');

        $path = '/Data/Files/';

        Layer::createDirectory($pathPrefix . $path);

        Layer::createDirectory($pathPrefix . $path . date('Y') . '/');

        Layer::createDirectory($pathPrefix . $path . date('Y') . '/' . date('m') . '/');

        $dir = $path . date('Y') . '/' . date('m') . '/' . date('d') . '/';

        Layer::createDirectory($pathPrefix . $dir);

        return $dir;
    }

    /**
     * Method stores file on disk
     *
     * @param string $fileContent
     *            content of the saving file
     * @param string $pathPrefix
     *            prefix to file
     * @param bool $decoded
     *            if the file was not encodded in base64
     * @return string Path to file
     */
    public function storeFileContent(string $fileContent, string $pathPrefix, bool $decoded = false): string
    {
        $dir = $this->prepareFs($pathPrefix);

        $fileName = md5((string) microtime(true));

        if ($decoded) {
            Layer::filePutContents($pathPrefix . $dir . $fileName, $fileContent);
        } else {
            Layer::filePutContents($pathPrefix . $dir . $fileName, base64_decode($fileContent));
        }

        return $pathPrefix . $dir . $fileName;
    }

    /**
     * Method stores file on disk
     *
     * @param string $filePath
     *            path to the saving file
     * @param string $pathPrefix
     *            prefix to file
     * @param bool $decoded
     *            if the file was not encodded in base64
     * @return string Path to file or null if the image was not loaded
     */
    public function storeFile(string $filePath, string $pathPrefix, bool $decoded = false): string
    {
        if (Layer::fileExists($filePath)) {
            $fileContent = Layer::existingFileGetContents($filePath);
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
    private function moveUploadedFile(string $from, string $to): void
    {
        move_uploaded_file($from, $to);
    }

    /**
     * Method returns file value
     *
     * @param string|array $value
     *            data about the uploaded file
     * @param bool $storeFiles
     *            Must be the file stored in the file system of the service or not
     * @param string $pathPrefix
     *            prefix of the file path
     * @return string|array path to the stored file or the array $value itself
     */
    public function getFileValue($value, bool $storeFiles, string $pathPrefix = '.')
    {
        if (is_string($value)) {
            /** @var array<string, array{size: int, name: string, tmp_name: string, file: string}> $_FILES */
            $value = $_FILES[$value];
        }

        /** @var array{size: int, name: string, tmp_name: string, file: string} $value */
        if (isset($value['size']) && $value['size'] === 0) {
            return '';
        }

        if ($storeFiles) {
            $dir = '.' . $this->prepareFs($pathPrefix);

            $uploadFile = $dir . md5($value['name'] . microtime(true)) . '.' .
                pathinfo($value['name'], PATHINFO_EXTENSION);

            if (isset($value['file'])) {
                Layer::filePutContents($uploadFile, base64_decode($value['file']));
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
     *            value to be made secure
     * @return string secure value
     */
    public function getStringValue($value): string
    {
        return htmlspecialchars((string) $value);
    }

    /**
     * Returning int value
     *
     * @param mixed $value
     *            value to be made secure
     * @return int secure value
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
     * @param ValidatorInterface[] $validators
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
