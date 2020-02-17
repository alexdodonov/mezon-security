<?php
namespace Mezon\Security;

/**
 * Class Security
 *
 * @package Mezon
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
     * @var \Mezon\Security\SecurityRules
     */
    public static $securityRules = null;

    /**
     * Method returns security rules
     *
     * @return \Mezon\Security\SecurityRules
     */
    public static function getSecurityRules(): \Mezon\Security\SecurityRules
    {
        if (self::$securityRules === null) {
            self::$securityRules = new \Mezon\Security\SecurityRules();
        }

        return self::$securityRules;
    }

    /**
     * Returning string value
     *
     * @param string $value
     *            Value to be made secure
     * @return string Secure value
     * @codeCoverageIgnore
     */
    public static function getStringValue(string $value): string
    {
        return self::getSecurityRules()->getStringValue($value);
    }

    /**
     * Method returns file value
     *
     * @param mixed $value
     *            Data about the uploaded file
     * @param bool $storeFiles
     *            Must be the file stored in the file system of the service or not
     * @return string|array Path to the stored file or the array $value itself
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
     *            Content of the saving file
     * @param string $pathPrefix
     *            Prefix to file
     * @param bool $decoded
     *            If the file was not encodded in base64
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
     *            Path to the saving file
     * @param string $pathPrefix
     *            Prefix to file
     * @param bool $decoded
     *            If the file was not encodded in base64
     * @return string Path to file or null if the image was not loaded
     * @codeCoverageIgnore
     */
    public static function storeFile(string $filePath, string $pathPrefix, bool $decoded = false): ?string
    {
        return self::getSecurityRules()->storeFile($filePath, $pathPrefix, $decoded);
    }
}
