<?php
namespace Mezon\Security\Validators\File;

/**
 * Class MimeType
 *
 * @package Mezon
 * @subpackage Security
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */
class MimeType implements \Mezon\Security\Validators\ValidatorInterface
{

    /**
     * Index in the $_FILES array
     *
     * @var string
     */
    private $file = '';

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
     * @see \Mezon\Security\Validators\ValidatorInterface::valid()
     */
    public function valid(): bool
    {
        if (isset($_FILES[$this->file]) === false) {
            throw (new \Exception('The index "' . $this->file . '" was not found in the $_FILES array', - 1));
        }

        return in_array(mime_content_type($_FILES[$this->file]['tmp_name']), $this->requiredTypes);
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