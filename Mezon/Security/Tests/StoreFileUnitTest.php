<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StoreFileUnitTest extends TestCase
{

    // TODO move it to the base class
    /**
     * Path to the directory where all files are stored
     *
     * @var string
     *
     */
    public const PATH_TO_FILE_STORAGE = '/data/files/';

    // TODO move it to the base class
    /**
     * Method returns path to storage
     *
     * @return string path to storage
     */
    protected function getPathToStorage(): string
    {
        return SecurityRulesUnitTest::PATH_TO_FILE_STORAGE . date('Y/m/d/');
    }

    /**
     * Testing 'storeFile' method
     */
    public function testStoreFile(): void
    {
        // setup
        $securityRules = new SecurityRulesMock();

        // test body
        $result = $securityRules->storeFile(__DIR__ . '/res/test.png', 'prefix');

        // assertions
        $this->assertStringContainsString($this->getPathToStorage(), $result);
    }
}
