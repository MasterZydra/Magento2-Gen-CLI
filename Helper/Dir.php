<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\DirectoryList;

class Dir extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @inheritdoc */
    public function __construct(
        Context $context,
        private DirectoryList $dirList,
    ) {
        parent::__construct($context);
    }

    /** Gets a filesystem path of the app directory */
    public function app(): string
    {
        return $this->dirList->getPath('app');
    }

    /** Gets a filesystem path of the app/code directory */
    public function appCode(): string
    {
        return $this->dirList->getPath('app') . DIRECTORY_SEPARATOR . 'code';
    }

    /** Gets a filesystem path of the root directory */
    public function root(): string
    {
        return $this->dirList->getRoot();
    }

    /** Gets a filesystem path of the template directory */
    public function template(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Template';
    }

    /** Gets a filesystem path of the custom templates directory*/
    public function customTemplate(): ?string
    {
        $template = getenv('GEN_CLI_TEMPLATE');
        if ($template === false) {
            return null;
        }
        return rtrim($template, '/\\');
    }
}
