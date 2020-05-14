<?php
namespace Mezon\Security\Validators;

/**
 * Class ValidatorInterface
 *
 * @package Mezon
 * @subpackage Security
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */

/**
 * Interface of the validator
 *
 * @author gdever
 */
interface ValidatorInterface
{

    /**
     * Method sets data to be validated
     *
     * @param mixed $data
     *            data to be validated
     */
    public function setValidatingData($data): void;

    /**
     * Method validates data
     *
     * @return bool true if the provided data is valid, false otherwise
     */
    public function valid(): bool;
}