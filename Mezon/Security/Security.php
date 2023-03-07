<?php
namespace Mezon\Security;

/**
 * Class Security
 *
 * @package Security
 * @subpackage Security
 * @author Dodonov A.A.
 * @version v.1.0 (2019/10/08)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Security class
 *
 * @author Dodonov A.A.
 */
class Security
{

    /**
     * Security rules
     *
     * @var ?SecurityRules
     */
    public static $securityRules = null;

    /**
     * Method returns security rules
     *
     * @return SecurityRules
     */
    public static function getSecurityRules(): SecurityRules
    {
        if (self::$securityRules === null) {
            self::$securityRules = new SecurityRules();
        }

        return self::$securityRules;
    }

    /**
     * Returning string value
     *
     * @param string $value
     *            value to be made secure
     * @return string secure value
     * @codeCoverageIgnore
     */
    public static function getStringValue(string $value): string
    {
        return self::getSecurityRules()->getStringValue($value);
    }

    /**
     * Returning int value
     *
     * @param mixed $value
     *            value to be made secure
     * @return int secure value
     * @codeCoverageIgnore
     */
    public static function getIntValue($value): int
    {
        return self::getSecurityRules()->getIntValue($value);
    }

    /**
     * Method returns file value
     *
     * @param string|array $value
     *            data about the uploaded file
     * @param bool $storeFiles
     *            must be the file stored in the file system of the service or not
     * @return string|array path to the stored file or the array $value itself
     * @codeCoverageIgnore
     */
    public static function getFileValue($value, bool $storeFiles)
    {
        return self::getSecurityRules()->getFileValue($value, $storeFiles);
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
     * @codeCoverageIgnore
     */
    public static function storeFileContent(string $fileContent, string $pathPrefix, bool $decoded = false): string
    {
        return self::getSecurityRules()->storeFileContent($fileContent, $pathPrefix, $decoded);
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
     * @codeCoverageIgnore
     */
    public static function storeFile(string $filePath, string $pathPrefix, bool $decoded = false): string
    {
        return self::getSecurityRules()->storeFile($filePath, $pathPrefix, $decoded);
    }
}
