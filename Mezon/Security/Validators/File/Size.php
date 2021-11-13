<?php
namespace Mezon\Security\Validators\File;

use Mezon\Security\Validators\ValidatorInterface;

/**
 * Class Size
 *
 * @package Security
 * @subpackage FileValidators
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */
class Size extends UploadingFile
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
     * @see ValidatorInterface::valid()
     */
    public function valid(): bool
    {
        $this->validateFilesFieldExists($this->getKey());

        /**
         *
         * @var array<string, string> $uploadingFile
         */
        $uploadingFile = $_FILES[$this->getKey()];

        return $uploadingFile['size'] <= $this->requiredSize;
    }
}