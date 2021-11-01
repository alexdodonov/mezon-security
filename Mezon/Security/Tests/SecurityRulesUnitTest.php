<?php
namespace Mezon\Security\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\Security\SecurityRules;
use Mezon\Security\Validators\File\Size;
use Mezon\Security\Validators\File\MimeType;
use Mezon\Security\Validators\File\ImageMaximumWidthHeight;
use Mezon\Security\Validators\File\ImageMinimumWidthHeight;
use Mezon\Conf\Conf;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SecurityRulesUnitTest extends TestCase
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
        'prepareFs',
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
        // TODO remove it and use SecurityRules
        $securityRules = $this->getMockBuilder(SecurityRules::class)
            ->onlyMethods(SecurityRulesUnitTest::FILE_SYSTEM_ACCESS_METHODS)
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
