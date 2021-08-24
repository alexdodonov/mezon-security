<?php
namespace Mezon\Security\Tests;

use Mezon\Security\SecurityRules;

class SecurityRulesMock extends SecurityRules
{

    /**
     *
     * {@inheritdoc}
     * @see \Mezon\Security\SecurityRules::prepareFs()
     */
    protected function prepareFs(string $pathPrefix): string
    {
        return '/data/files/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
    }

    /**
     * File path
     *
     * @var string
     */
    public $putContentsFilePath = '';

    /**
     *
     * {@inheritdoc}
     * @see \Mezon\Security\SecurityRules::filePutContents()
     */
    protected function filePutContents(string $file, string $content): void
    {
        $this->putContentsFilePath = $file;
    }
}
