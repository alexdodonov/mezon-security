<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StoreUnexistingFileUnitTest extends TestCase
{

    /**
     * Testing 'storeFile' method for unexisting file
     */
    public function testStoreUnexistingFile(): void
    {
        // setup
        $securityRules = new SecurityRulesMock();

        // assertions
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The file c://file was not found');

        // test body
        $securityRules->storeFile('c://file', 'prefix');
    }
}
