<?php
require_once (__DIR__ . '/../SecurityRules.php');

class SecurityRulesUnitTest extends \PHPUnit\Framework\TestCase
{

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
                    'test-file' => [
                        'size' => 1,
                        'file' => '1',
                        'name' => '1'
                    ]
                ]
            ],
            [
                false,
                [
                    'test-file' => [
                        'size' => 1,
                        'file' => '1',
                        'name' => '1'
                    ]
                ]
            ],
            [
                true,
                [
                    'test-file' => [
                        'size' => 1,
                        'tmp_name' => '1',
                        'name' => '1'
                    ]
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
            $this->assertStringContainsString('/data/files/' . date('Y/m/d/'), $result);
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
        $result = $securityRules->storeFileContent('content', 'prefix', $decoded);

        // assertions
        $this->assertStringContainsString('/data/files/' . date('Y/m/d/'), $result);
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
        $this->assertStringContainsString('/data/files/' . date('Y/m/d/'), $result);
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
}
