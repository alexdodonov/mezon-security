<?php
namespace Mezon\Security\Validators;

/**
 * Class AbstractValidator
 *
 * @package Mezon
 * @subpackage Security
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */

/**
 * Abstract class for validators
 *
 * @author gdever
 */
abstract class AbstractValidator implements ValidatorInterface
{

    /**
     * Method validates that $_FILES[$field] exists.
     * If it does not then exception will be thrown
     *
     * @param string $field
     *            field name
     */
    public function validateFilesFieldExists(string $field): void
    {
        if (isset($_FILES[$field]) === false) {
            throw (new \Exception('The index "' . $field . '" was not found in the $_FILES array', - 1));
        }
    }
}