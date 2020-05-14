<?php
namespace Mezon\Security\Validators\File;

/**
 * Class ImageMaximumWidthHeight
 *
 * @package Mezon
 * @subpackage Security
 * @author Dodonov A.A.
 * @version v.1.0 (2020/05/13)
 * @copyright Copyright (c) 2020, aeon.org
 */
class ImageMaximumWidthHeight extends \Mezon\Security\Validators\AbstractValidator
{

    /**
     * Index in the $_FILES array
     *
     * @var string
     */
    private $file = '';

    /**
     * Maximum width of the image
     *
     * @var integer
     */
    private $maximumWidth = 0;

    /**
     * Maximum height of the image
     *
     * @var integer
     */
    private $maximumHeight = 0;

    /**
     * Constructor
     *
     * @param int $width
     *            width constraint for the file
     * @param int $height
     *            height constraint for the file
     * @codeCoverageIgnore
     */
    public function __construct(int $width, int $height)
    {
        $this->maximumWidth = $width;

        $this->maximumHeight = $height;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Mezon\Security\Validators\ValidatorInterface::valid()
     */
    public function valid(): bool
    {
        $this->validateFilesFieldExists($this->file);

        list($width, $height) = getimagesize($_FILES[$this->file]['tmp_name']);

        return $width <= $this->maximumWidth && $height <= $this->maximumHeight;
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