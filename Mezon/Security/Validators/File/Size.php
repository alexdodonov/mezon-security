<?php
namespace Mezon\Security\Validators\File;

use Mezon\Security\Validators\AbstractValidator;

/**
 * Class Size
 *
 * @package Mezon
 * @subpackage Security
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */
class Size extends AbstractValidator
{

    /**
     * Bytes in KB
     *
     * @var integer
     */
    public const KB = 1024;

    /**
     * Bytes in MB
     *
     * @var integer
     */
    public const MB = 1048576;

    /**
     * Bytes in GB
     *
     * @var integer
     */
    public const GB = 1073741824;

    /**
     * Index in the $_FILES array
     *
     * @var string
     */
    private $file = '';

    /**
     * Required size in bytes
     *
     * @var integer
     */
    private $requiredSize = 0;

    /**
     * Constructor
     *
     * @param int $size
     *            size constraint for the file
     * @codeCoverageIgnore
     */
    public function __construct(int $size)
    {
        $this->requiredSize = $size;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Mezon\Security\Validators\ValidatorInterface::valid()
     */
    public function valid(): bool
    {
        $this->validateFilesFieldExists($this->file);

        return $_FILES[$this->file]['size'] <= $this->requiredSize;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Mezon\Security\Validators\ValidatorInterface::setValidatingData()
     */
    public function setValidatingData($data): void
    {
        $this->file = $data;
    }
}