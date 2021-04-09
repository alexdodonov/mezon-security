<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Security\SecurityRules;
use Mezon\Security\Validators\File\Size;
use Mezon\Security\Validators\File\MimeType;
use Mezon\Security\Validators\File\ImageMaximumWidthHeight;
use Mezon\Security\Validators\File\ImageMinimumWidthHeight;

class SecurityRulesUnitTest extends TestCase
{

    /**
     * Path to the directory where all files are stored
     *
     * @var string
     */
    public const PATH_TO_FILE_STORAGE = '/data/files/';

    /**
     * Field name of the testing file
     *
     * @var string
     */
    public const TEST_FILE_FIELD_NAME = 'test-file';

    /**
     * Testing image
     *
     * @var string
     */
    public const TEST_PNG_IMAGE_PATH = __DIR__ . '/res/test.png';

    /**
     * List of methods wich provides file system access and need to be mocked
     *
     * @var array
     */
    public const FILE_SYSTEM_ACCESS_METHODS = [
        '_prepareFs',
        'filePutContents',
        'moveUploadedFile',
        'fileGetContents'
    ];

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
        $securityRules = $this->getMockBuilder(SecurityRules::class)
            ->setMethods(SecurityRulesUnitTest::FILE_SYSTEM_ACCESS_METHODS)
            ->setConstructorArgs([])
            ->getMock();

        // test body
        $result = $securityRules->getFileValue('empty-file', false);

        // assertions
        $this->assertEquals('', $result);
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
     */
    public function testGetFileValue(bool $storeFile, array $files): void
    {
        // setup
        $_FILES = $files;
        $securityRules = $this->getMockBuilder(SecurityRules::class)
            ->setMethods(SecurityRulesUnitTest::FILE_SYSTEM_ACCESS_METHODS)
            ->setConstructorArgs([])
            ->getMock();

        if ($storeFile) {
            if ($this->tmpNameSet($files[SecurityRulesUnitTest::TEST_FILE_FIELD_NAME])) {
                $securityRules->expects($this->once())
                    ->method('moveUploadedFile');
            } else {
                $securityRules->expects($this->once())
                    ->method('filePutContents');
            }
        }

        // test body
        $result = $securityRules->getFileValue(SecurityRulesUnitTest::TEST_FILE_FIELD_NAME, $storeFile);

        // assertions
        if ($storeFile) {
            $this->assertStringContainsString($this->getPathToStorage(), $result);
        } else {
            $this->assertEquals(1, $result['size']);

            $this->assertEquals('1', $result['name']);
            if ($this->tmpNameSet($files[SecurityRulesUnitTest::TEST_FILE_FIELD_NAME])) {
                $this->assertEquals('1', $result['tmp_name']);
            } else {
                $this->assertEquals('1', $result['file']);
            }
        }
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
        $securityRules = $this->getMockBuilder(SecurityRules::class)
            ->setMethods(SecurityRulesUnitTest::FILE_SYSTEM_ACCESS_METHODS)
            ->setConstructorArgs([])
            ->getMock();
        $securityRules->method('_prepareFs')->willReturn('prepared');
        $securityRules->expects($this->once())
            ->method('filePutContents');

        // test body
        $result = $securityRules->storeFileContent('content', 'file-prefix', $decoded);

        // assertions
        $this->assertStringContainsString($this->getPathToStorage(), $result);
    }

    /**
     * Mock creation function
     *
     * @param mixed $returnValue
     *            return value of the fileGetContents method
     * @return object mock
     */
    protected function getStoreFileMock($returnValue): object
    {
        $securityRules = $this->getMockBuilder(SecurityRules::class)
            ->setMethods(SecurityRulesUnitTest::FILE_SYSTEM_ACCESS_METHODS)
            ->setConstructorArgs([])
            ->getMock();
        $securityRules->expects($this->once())
            ->method('fileGetContents')
            ->willReturn($returnValue);
        $securityRules->method('_prepareFs')->willReturn('prepared');

        return $securityRules;
    }

    /**
     * Testing 'storeFile' method
     */
    public function testStoreFile(): void
    {
        // setup
        $securityRules = $this->getStoreFileMock('content');

        // test body
        $result = $securityRules->storeFile('c://file', 'prefix');

        // assertions
        $this->assertStringContainsString($this->getPathToStorage(), $result);
    }

    /**
     * Testing 'storeFile' method for unexisting file
     */
    public function testStoreUnexistingFile(): void
    {
        // setup
        $securityRules = $this->getStoreFileMock(false);

        // test body
        $result = $securityRules->storeFile('c://file', 'prefix');

        // assertions
        $this->assertNull($result);
    }

    /**
     * Data provider for the test testIsUploadedFileValid
     *
     * @return array testing data
     */
    public function isUploadFileValidProvider(): array
    {
        return [
            [
                $this->constructUploadedFile(2000, '1', '1'),
                [],
                true
            ],
            [
                $this->constructUploadedFile(2000, '1', '1'),
                [
                    new Size(2000)
                ],
                true
            ],
            [
                $this->constructUploadedFile(2000, '1', '1'),
                [
                    new Size(1500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(2000, '1', '1', __DIR__ . '/SecurityRulesUnitTest.php'),
                [
                    new MimeType([
                        'text/x-php'
                    ])
                ],
                true
            ],
            [
                $this->constructUploadedFile(2000, '1', '1', __DIR__ . '/SecurityRulesUnitTest.php'),
                [
                    new MimeType([
                        'image/png'
                    ])
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new MimeType([
                        'image/png'
                    ])
                ],
                true
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMaximumWidthHeight(500, 500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMaximumWidthHeight(500, 600)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMaximumWidthHeight(500, 500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMaximumWidthHeight(600, 600)
                ],
                true
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMinimumWidthHeight(500, 500)
                ],
                true
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMinimumWidthHeight(600, 500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMinimumWidthHeight(500, 600)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new ImageMinimumWidthHeight(600, 600)
                ],
                false
            ]
        ];
    }

    /**
     * Testing that uploaded file is valid
     *
     * @param array $file
     *            uploaded file
     * @param array $validators
     *            validators
     * @param bool $requiredResult
     *            required result
     * @dataProvider isUploadFileValidProvider
     */
    public function testIsUploadedFileValid(array $file, array $validators, bool $requiredResult): void
    {
        // setup
        $security = new SecurityRules();
        $_FILES['is-valid-file'] = $file;

        // test body
        $result = $security->isUploadedFileValid('is-valid-file', $validators);

        // assertions
        $this->assertEquals($requiredResult, $result);
    }

    /**
     * Data provider for the test testValidatingUnexistingFile
     *
     * @return array testing data
     */
    public function validatingUnexistingFileProvider(): array
    {
        return [
            [
                new Size(2000)
            ],
            [
                new MimeType([
                    'image/jpeg'
                ])
            ]
        ];
    }

    /**
     * Trying to validate size of the unexisting file
     *
     * @param object $validator
     *            validator
     * @dataProvider validatingUnexistingFileProvider
     */
    public function testValidatingUnexistingFile(object $validator): void
    {
        // assertions
        $this->expectException(\Exception::class);

        // setup
        $security = new SecurityRules();

        // test body
        $security->isUploadedFileValid('unexisting-file', [
            $validator
        ]);
    }

    /**
     * Testing getIntValue method
     */
    public function testGetIntValue(): void
    {
        // setup
        $security = new SecurityRules();

        // test body and assertions
        $this->assertEquals(1, $security->getIntValue('1'));
        $this->assertEquals(2, $security->getIntValue(2));
        $this->assertEquals(0, $security->getIntValue('abc'));
        $this->assertEquals(1, $security->getIntValue(1.1));
    }

    /**
     * Testing getStringValue
     */
    public function testGetStringValue(): void
    {
        // setup
        $security = new SecurityRules();

        // test body and assertions
        $this->assertEquals('1', $security->getStringValue('1'));
        $this->assertEquals('1', $security->getStringValue(1));
        $this->assertEquals('2.1', $security->getStringValue(2.1));
        $this->assertEquals('&amp;', $security->getStringValue('&'));
        $this->assertEquals('&lt;', $security->getStringValue('<'));
        $this->assertEquals('&gt;', $security->getStringValue('>'));
    }
}
