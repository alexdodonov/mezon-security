<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Conf\Conf;
use Mezon\Security\SecurityRules;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StoreFileUnitTest extends TestCase
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

    // TODO move it to the base class
    /**
     * Path to the directory where all files are stored
     *
     * @var string
     *
     */
    public const PATH_TO_FILE_STORAGE = '/Data/Files/';

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
        $securityRules = new SecurityRules();

        // test body
        $result = $securityRules->storeFile(__DIR__ . '/res/test.png', 'prefix');

        // assertions
        $this->assertStringContainsString($this->getPathToStorage(), $result);
    }
}
