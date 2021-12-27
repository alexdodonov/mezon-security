<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Security\SecurityRules;
use Mezon\Security\Validators\File\Size;
use Mezon\Security\Validators\File\MimeType;
use Mezon\Security\Validators\File\ImageMaximumWidthHeight;
use Mezon\Security\Validators\File\ImageMinimumWidthHeight;
use Mezon\Conf\Conf;
use Mezon\Security\Validators\ValidatorInterface;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetEmptyFieldValueUnitTest extends TestCase
{

    /**
     *
     * {@inheritdoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        Conf::setConfigValue('fs/layer', 'mock');
    }

    /**
     * Testing edge cases of getFileValue
     */
    public function testGetEmptyFileValue(): void
    {
        // setup
        $_FILES = [
            'empty-file' => [
                'size' => 0
            ]
        ];
        $securityRules = new SecurityRules();

        // test body
        $result = $securityRules->getFileValue('empty-file', false);

        // assertions
        $this->assertEquals('', $result);
    }
}
