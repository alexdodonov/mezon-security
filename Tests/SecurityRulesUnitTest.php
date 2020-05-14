<?php
namespace Mezon\Security\Tests;

class SecurityRulesUnitTest extends \PHPUnit\Framework\TestCase
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
    public const TEST_PNG_IMAGE_PATH = __DIR__.'/res/test.png';

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
        $securityRules = $this->getMockBuilder(\Mezon\Security\SecurityRules::class)
            ->setMethods([
            '_prepareFs',
            'filePutContents',
            'moveUploadedFile'
        ])
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
        $securityRules = $this->getMockBuilder(\Mezon\Security\SecurityRules::class)
            ->setMethods([
            '_prepareFs',
            'filePutContents',
            'moveUploadedFile'
        ])
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
        $securityRules = $this->getMockBuilder(\Mezon\Security\SecurityRules::class)
            ->setMethods([
            '_prepareFs',
            'filePutContents',
            'moveUploadedFile'
        ])
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
        $securityRules = $this->getMockBuilder(\Mezon\Security\SecurityRules::class)
            ->setMethods([
            'fileGetContents',
            '_prepareFs',
            'filePutContents',
        ])
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
                    new \Mezon\Security\Validators\File\Size(2000)
                ],
                true
            ],
            [
                $this->constructUploadedFile(2000, '1', '1'),
                [
                    new \Mezon\Security\Validators\File\Size(1500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(2000, '1', '1', __DIR__ . '/SecurityRulesUnitTest.php'),
                [
                    new \Mezon\Security\Validators\File\MimeType([
                        'text/x-php'
                    ])
                ],
                true
            ],
            [
                $this->constructUploadedFile(2000, '1', '1', __DIR__ . '/SecurityRulesUnitTest.php'),
                [
                    new \Mezon\Security\Validators\File\MimeType([
                        'image/png'
                    ])
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\MimeType([
                        'image/png'
                    ])
                ],
                true
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMaximumWidthHeight(500, 500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMaximumWidthHeight(500, 600)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMaximumWidthHeight(500, 500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMaximumWidthHeight(600, 600)
                ],
                true
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMinimumWidthHeight(500, 500)
                ],
                true
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMinimumWidthHeight(600, 500)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMinimumWidthHeight(500, 600)
                ],
                false
            ],
            [
                $this->constructUploadedFile(6912, '1', '1', SecurityRulesUnitTest::TEST_PNG_IMAGE_PATH),
                [
                    new \Mezon\Security\Validators\File\ImageMinimumWidthHeight(600, 600)
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
        $security = new \Mezon\Security\SecurityRules();
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
                new \Mezon\Security\Validators\File\Size(2000)
            ],
            [
                new \Mezon\Security\Validators\File\MimeType([
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
        $security = new \Mezon\Security\SecurityRules();

        // test body
        $security->isUploadedFileValid('unexisting-file', [
            $validator
        ]);
    }
}
