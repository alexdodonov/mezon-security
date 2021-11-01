<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Conf\Conf;
use Mezon\Security\SecurityRules;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StoreFileContentUnitTest extends TestCase
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
     * Data provider for the testStoreFileContent
     *
     * @return array data for the testStoreFileContent test
     */
    public function storeFileContentProvider(): array
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }

    /**
     * Testing storeFileContent method
     *
     * @dataProvider storeFileContentProvider
     */
    public function testStoreFileContent(bool $decoded): void
    {
        // setup
        $securityRules = new SecurityRules();

        // test body
        $result = $securityRules->storeFileContent('content', 'file-prefix', $decoded);

        // assertions
        $this->assertStringContainsString($this->getPathToStorage(), $result);
    }
}
