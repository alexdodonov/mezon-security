<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Conf\Conf;
use Mezon\Security\SecurityRules;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetFileValueUnitTest extends TestCase
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

    // TOD move to the base class
    /**
     * Field name of the testing file
     *
     * @var string
     */
    public const TEST_FILE_FIELD_NAME = 'test-file';

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
     * Method constructs element of the $_FILES array
     *
     * @param int $size
     *            size of the file
     * @param string $file
     *            file path
     * @param string $name
     *            file name
     * @return array element of the $_FILES array
     */
    protected function constructUploadedFile(int $size, string $file, string $name, string $tmpName = ''): array
    {
        // TODO move this method to the base class
        $return = [
            'size' => $size,
            'name' => $name
        ];

        if ($file !== '') {
            $return['file'] = $file;
        }

        if ($tmpName !== '') {
            $return['tmp_' . 'name'] = $tmpName;
        }

        return $return;
    }

    /**
     * Method constructs $_FILES array with one element
     *
     * @param int $size
     *            size of the file
     * @param string $file
     *            file path
     * @param string $name
     *            file name
     * @return array element of the $_FILES array
     */
    protected function constructTestFiles(int $size, string $file, string $name, string $tmpName = ''): array
    {
        return [
            SecurityRulesUnitTest::TEST_FILE_FIELD_NAME => $this->constructUploadedFile($size, $file, $name, $tmpName)
        ];
    }

    /**
     * Data provider for the testGetFileValue test
     *
     * @return array data for test testGetFileValue
     */
    public function getFileValueProvider(): array
    {
        return [
            [
                true,
                $this->constructTestFiles(2000, '1', '1')
            ],
            [
                false,
                $this->constructTestFiles(1, '1', '1')
            ],
            [
                true,
                $this->constructTestFiles(1, '', '1', '1')
            ]
        ];
    }

    /**
     * Method returns true if the field tmp_name is set
     *
     * @param array $file
     *            validating file description
     * @return bool true if the field tmp_name is set, false otherwise
     */
    protected function tmpNameSet(array $file): bool
    {
        return isset($file['tmp_name']);
    }

    /**
     * Testing edge cases of getFileValue
     *
     * @param bool $storeFile
     *            do we need to store file
     * @param array $files
     *            file ddescription
     * @dataProvider getFileValueProvider
     * @psalm-suppress PossiblyInvalidArgument, PossiblyInvalidArrayOffset
     */
    public function testGetFileValue(bool $storeFile, array $files): void
    {
        // setup
        $_FILES = $files;
        $securityRules = new SecurityRules();

        // test body
        $result = $securityRules->getFileValue(GetFileValueUnitTest::TEST_FILE_FIELD_NAME, $storeFile);

        // assertions
        if ($storeFile) {
            $this->assertStringContainsString($this->getPathToStorage(), $result);
        } else {
            $this->assertEquals(1, $result['size']);

            $this->assertEquals('1', $result['name']);
            if ($this->tmpNameSet($files[GetFileValueUnitTest::TEST_FILE_FIELD_NAME])) {
                $this->assertEquals('1', $result['tmp_name']);
            } else {
                $this->assertEquals('1', $result['file']);
            }
        }
    }
}
