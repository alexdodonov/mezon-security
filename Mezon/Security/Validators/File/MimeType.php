<?php
namespace Mezon\Security\Validators\File;

use Mezon\Security\Validators\ValidatorInterface;

/**
 * Class MimeType
 *
 * @package Security
 * @subpackage FileValidators
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */
class MimeType extends UploadingFile
{

    /**
     * Available mime types
     *
     * @var array
     */
    private $requiredTypes = [];

    /**
     * Constructor
     *
     * @param array $types
     *            mime type constraint for the file
     * @codeCoverageIgnore
     */
    public function __construct(array $types)
    {
        $this->requiredTypes = $types;
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

        return in_array(mime_content_type($uploadingFile['tmp_name']), $this->requiredTypes);
    }
}