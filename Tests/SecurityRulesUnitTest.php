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
            'test-file' => [
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
        $result = $securityRules->getFileValue('test-file', false);

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
            $return['tmp_name'] = $tmpName;
        }

        return $return;
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
                [
                    'test-file' => $this->constructUploadedFile(2000, '1', '1')
                ]
            ],
            [
                false,
                [
                    'test-file' => $this->constructUploadedFile(1, '1', '1')
                ]
            ],
            [
                true,
                [
                    'test-file' => $this->constructUploadedFile(1, '', '1', '1')
                ]
            ]
        ];
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
            if (isset($files['test-file']['tmp_name'])) {
                $securityRules->expects($this->once())
                    ->method('moveUploadedFile');
            } else {
                $securityRules->expects($this->once())
                    ->method('filePutContents');
            }
        }

        // test body
        $result = $securityRules->getFileValue('test-file', $storeFile);

        // assertions
        if ($storeFile) {
            $this->assertStringContainsString($this->getPathToStorage(), $result);
        } else {
            $this->assertEquals(1, $result['size']);

            $this->assertEquals('1', $result['name']);
            if (isset($files['test-file']['tmp_name'])) {
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
     * @dataProvider isUPloadFileValidProvider
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
     * Trying to validate size of the unexisting file
     */
    public function testValidatingSizeOfUnexistingFile(): void
    {
        // assertions
        $this->expectException(\Exception::class);

        // setup
        $security = new \Mezon\Security\SecurityRules();
        $validators = [
            new \Mezon\Security\Validators\File\Size(2000)
        ];

        // test body
        $security->isUploadedFileValid('unexisting-file', $validators);
    }
}
