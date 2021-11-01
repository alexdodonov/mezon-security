<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Conf\Conf;
use Mezon\Security\SecurityRules;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StoreUnexistingFileUnitTest extends TestCase
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
     * Testing 'storeFile' method for unexisting file
     */
    public function testStoreUnexistingFile(): void
    {
        // setup
        $securityRules = new SecurityRules();

        // assertions
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The file c://file was not found');

        // test body
        $securityRules->storeFile('c://file', 'prefix');
    }
}
