<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Security\Validators\File\Size;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class UploadingFileUnitTest extends TestCase
{

    /**
     * Testing exception while setting data
     */
    public function testException(): void
    {
        // assertions
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(- 1);
        $this->expectExceptionMessage('Invalid type for $_FILES key');

        // setup
        // here we use Size because UploadingFile is abstract
        $validator = new Size(100);

        // test body
        $validator->setValidatingData(new \stdClass());
    }
}
