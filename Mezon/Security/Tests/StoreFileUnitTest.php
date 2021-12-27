<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Conf\Conf;
use Mezon\Security\SecurityRules;
use Mezon\Fs\Layer;
use Mezon\Fs\InMemory;

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
        Layer::$existingFiles[] = __DIR__ . '/res/test.png';
        InMemory::preloadFile(__DIR__ . '/res/test.png');
        $securityRules = new SecurityRules();

        // test body
        $result = $securityRules->storeFile(__DIR__ . '/res/test.png', 'prefix');

        // assertions
        $this->assertStringContainsString($this->getPathToStorage(), $result);
        $this->assertTrue(Layer::directoryExists('prefix/Data/'));
        $this->assertTrue(Layer::directoryExists('prefix/Data/Files/'));
    }
}
