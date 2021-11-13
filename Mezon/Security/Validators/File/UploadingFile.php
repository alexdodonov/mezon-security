<?php
namespace Mezon\Security\Validators\File;

use Mezon\Security\Validators\AbstractValidator;
use Mezon\Security\Validators\ValidatorInterface;

/**
 * Class Size
 *
 * @package Security
 * @subpackage FileValidators
 * @author Dodonov A.A.
 * @version v.1.0 (2021/11/13)
 * @copyright Copyright (c) 2021, aeon.org
 */
abstract class UploadingFile extends AbstractValidator
{

    /**
     * Index in the $_FILES array
     *
     * @var string
     */
    private $file = '';

    /**
     *
     * {@inheritdoc}
     * @see ValidatorInterface::setValidatingData()
     */
    public function setValidatingData($data): void
    {
        if (! is_string($data)) {
            throw (new \Exception('Invalid type for $_FILES key', - 1));
        }

        $this->file = $data;
    }

    /**
     * Getter for the field $this->file
     *
     * @return string field $this->file
     */
    public function getKey(): string
    {
        return $this->file;
    }
}